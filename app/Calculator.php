<?php
namespace App;

use App\CurrencyCalculation;
use App\CurrencyRate;
use Illuminate\Support\Facades\Cache;

/**
 * Utility class that provides the core services for this app
 *
 */
class Calculator
{
    // used for storing results
    protected $weeks;
    protected $chartData;
    protected $hilo;

    // used internally
    protected $parameters;
    protected $latestDate;
    protected $latest;

    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Prepare the calculation results for rendering.
     *
     * @param \App\CurrencyCalculation $calculation
     */
    public function prepare(CurrencyCalculation $calculation)
    {
ini_set('max_execution_time', 0);
        $this->parameters = $calculation;
        $today = date('Y-m-d');
        $weekNumber = date('W');

        // retrieve cached calculation results from db
        $weeks = $calculation->weeks()
            ->orderBy('year', 'desc')       // order
            ->orderBy('week', 'desc')
            ->take($calculation->duration)  // limit
            ->get()                         // fetch
            ->reverse();                    // oldest first

        // determine if we need to generate/update results cache
        $needsUpdate = false;
        $latest = null;
        $latestDate = $this->getLatest()->first()->date;
        if ($weeks->count() > 0) {
            $latest = $weeks->last();
            $nextMonday = date('Y-m-d', strtotime('next Monday ' . $latest->last_day));

            // update incomplete week if we can
            if (!$latest->complete && strcmp($latestDate, $latest->last_day) > 0) {
                $week = date('Y-m-d', strtotime($nextMonday . ' -1 Week'));
                $latest->fill($this->calculateWeek($week));
                $latest->save();
            }

            // set the week pointer to next week from last in the cache
            if ($latest->week !== $weekNumber) {
                $needsUpdate = $nextMonday;
            }
        } else {
            // set the week pointer to first week we need to include in the report
            $needsUpdate = date('Y-m-d', strtotime($latestDate . ' Monday this Week -' . $calculation->duration . ' Weeks '));
        }

        // update results
        while ($needsUpdate && strtotime($needsUpdate) < strtotime($latestDate)) {
            $data = $this->calculateWeek($needsUpdate);
            if ($data) {
                // create new week
                $week = $calculation->weeks()->create($data);

                // add week data to collection
                $weeks->push($week);
            }

            // increment week pointer
            $needsUpdate = date('Y-m-d', strtotime($needsUpdate . ' +1 Week'));
        }

        // prepare chart data
        $chartData = \Lava::DataTable();
        $chartData->addDateColumn('Week')
            ->addNumberColumn('Highest')
            ->addNumberColumn('Lowest');

        // get latest rate for profit calculation
        $latestRate = $this->getCurrencyRate(
            $this->getLatest(),
            $this->parameters->base,
            $this->parameters->target
        );

        // populate chart data & find highest and lowest weeks & calculate profits
        $hi = $lo = $h = $l = 0;
        foreach ($weeks as $week) {
            // add chart row
            $w = sprintf('%d-W%02d', $week->year, $week->week);
            $day = date('Y-m-d', strtotime($w));
            $chartData->addRow([$day, $week->rate_max, $week->rate_min]);

            // update hilo
            if (!$h || $week->amount > $h) {
                $h = $week->amount;
                $hi = $week->id;
            }
            if (!$l || $week->amount < $l) {
                $l = $week->amount;
                $lo = $week->id;
            }

            // profit
            $week->profit = $week->amount - $this->parameters->amount * $latestRate;
        }

        // store results
        $this->weeks = $weeks;
        $this->chartData = $chartData;
        $this->hilo = [$hi, $lo];
    }

    /**
     * Provides a collection of CalculationWeek model objects.
     * Use when providing data for the view that renders the results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function weeks()
    {
        return $this->weeks;
    }

    /**
     * Provides the chart data for the view.
     *
     * @return DataTable [FIXDOC]
     */
    public function chart()
    {
        return $this->chartData;
    }

    /**
     * Return highest/lowest historical week IDs
     *
     * @return array
     */
    public function hilo()
    {
        return $this->hilo;
    }

    /**
     * Return date of given weekday from the same week as given date.
     *
     * @param string $date
     * @param int $weekday
     * @return string
     */
    public static function weekdayDate($date, $weekday = 1)
    {
        $dayofweek = date('w', strtotime($date));
        $result    = date('Y-m-d', strtotime(($day - $dayofweek).' day', strtotime($date)));
    }

    /**
     * Get rates for the given date. Queries the API only if necessary.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRates($date, $forceApi = false)
    {
        if (!$forceApi) {
            $rates = CurrencyRate::where('date', $date)->get();
            if (!$rates->isEmpty()) {
                // this check provides sanity check against incomplete rate data
                if ($rates->count() !== count(config('app.currencies'))) {
                    // clear all rows for the given date, then proceed to query the API
                    $rates->each(function ($row) {
                        $row->delete();
                    });
                } else {
                    // otherwise we're good, return cached rates
                    return $rates;
                }
            }
        }

        // query via API
        return $this->query($date);
    }

    /**
     * Get latest rates. Queries the API only if necessary.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getLatest()
    {
        if ($this->latest) {
            return $this->latest;
        }

        if (!$this->isApiQueryNeeded()) {
            return $this->latest = CurrencyRate::where('date', $this->latestDate)->get();
        }

        // query the API for latest rates
        return $this->latest = $this->query();
    }

    /**
     * Determine if we need to make a query to the currency rate API.
     *
     * @return boolean
     */
    protected function isApiQueryNeeded()
    {
        $latest = CurrencyRate::orderBy('date', 'desc')->take(1)->get();

        // nothing in the db
        if ($latest->isEmpty()) {
            return true;
        }

        // store date for further queries
        $date = $this->latestDate = $latest->first()->date;

        // today's rates already in the db
        if ($date == date('Y-m-d')) {
            return false;
        }

        // latest weekday result already in the db
        if (strtotime($date . ' +1 Weekday') > time()) {
            return false;
        }

        // not enough time passed since last check
        if (Cache::get('currency.apicheck')) {
            return false;
        }

        // if we reached here, we need to query the api
        Cache::put('currency.apicheck', 'true', config('app.currency_apiquery_cache_expire_time'));
        return true;
    }

    /**
     * Queries the API for rates for the given date.
     *
     * @param mixed $date Omit to query for latest rates.
     * @return \Illuminate\Support\Collection
     */
    protected function query($date = null)
    {
        // run api query
        // DUMMY RESULTS FOR NOW
        $date = $date ?: date('Y-m-d');
        $res = [];
        foreach (config('app.currencies') as $currency) {
            $res[$currency] = mt_rand(10000, 150000) / 100000;
        }
        $results = new \Ultraleet\CurrencyRates\Result('EUR', new \DateTime($date), $res);

        // verify that we got the results we asked for
        if ($date && $results->date->format('Y-m-d') !== $date) {
            return null;
        }

        // store results in the db
        $rates = collect([]);
        foreach ($results->rates as $currency => $value) {
            $rate = new CurrencyRate;
            $rate->date = $date;
            $rate->currency = $currency;
            $rate->rate = $value;
            $rate->save();

            // add to collection
            $rates->push($rate);
        }

        // return rates collection
        return $rates;
    }

    /**
     * Get currency rate from a collection of base rates.
     *
     * @param \Illuminate\Support\Collection $rates
     * @param string $base Base currency.
     * @param string $target Target currency.
     * @return float
     */
    protected function getCurrencyRate($rates, $base, $target)
    {
        // if base is the same as in the database, return rate as is
        if ($base === config('app.default_currencies')[0]) {
            $rate = $rates->first(function ($model) use ($target) {
                return $model->currency === $target;
            });

            return $rate->rate;
        }

        // if target is the same as database base, return inverted rate
        if ($target === config('app.default_currencies')[0]) {

            $rate = $rates->first(function ($model) use ($base) {
                return $model->currency === $base;
            });

            return 1 / $rate->rate;
        }

        // otherwise, rebase the rate
        $rateBase = $rates->first(function ($model) use ($base) {
            return $model->currency === $base;
        });
        $rateTarget = $rates->first(function ($model) use ($target) {
            return $model->currency === $target;
        });

        return $rateTarget->rate / $rateBase->rate;
    }

    /**
     * Calculate results for a given week.
     *
     * @param string $monday Monday of the week to calculate.
     * @return array|null CalculationWeek attributes that can be used for mass assignment.
     */
    protected function calculateWeek($monday)
    {
        $params = $this->parameters;
        $rateMax = $rateMin = 0;

        // get rates for all working days of the week
        for ($i = 0; $i < 5; $i++) {
            $date = $i ? date('Y-m-d', strtotime("$monday +$i Days")) : $monday;
            if (strcmp($date, $this->latestDate) > 0) {
                break;
            }

            $results = $this->getRates($date);
            if ($results) {
                $lastDay = $date;

                $rate = $this->getCurrencyRate(
                    $results,
                    $this->parameters->base,
                    $this->parameters->target
                );

                // update min rate for the week
                if (!$rateMin || $rate < $rateMin) {
                    $rateMin = $rate;
                }

                // update max rate for the week
                if ($rate > $rateMax) {
                    $rateMax = $rate;
                }
            };
        }

        // no rates found, abort
        if (!isset($lastDay)) {
            return null;
        }

        // determine if this week has been completed
        $complete = strcmp($this->latestDate, $date) > 0 // not current week
                 || date('w', strtotime($date)) === 4;   // last day is friday

        // return data
        return [
            'year' => date('Y', strtotime($monday)),
            'week' => date('W', strtotime($monday)),
            'rate' => $rate, // this will be set to the rate of the last recorded day
            'rate_min' => $rateMin,
            'rate_max' => $rateMax,
            'amount' => $params->amount * $rate,
            'complete' => $complete,
            'last_day' => $lastDay,
        ];
    }
}
