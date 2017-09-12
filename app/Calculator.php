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
    protected $rates;
    protected $weeks;
    protected $chartData;
    protected $hilo;

    // counters
    protected $totalQueries = 0;
    protected $apiQueries = 0;

    // used internally
    protected $parameters;
    protected $latestDate;
    protected $latest;
    protected $dbBase;

    /**
     * Constructor
     */
    public function __construct()
    {
        // store default base currency
        $this->dbBase = config('app.default_currencies')[0];

        // initialize
        $this->rates = collect([]);
    }

    /**
     * Prepare the calculation results for rendering.
     *
     * @param \App\CurrencyCalculation $calculation
     */
    public function prepare(CurrencyCalculation $calculation)
    {
        $this->parameters = $calculation;
        $today = date('Y-m-d');
        $weekNumber = date('W');

        // retrieve cached calculation results from db
        $this->weeks = $calculation->weeks()
            ->orderBy('year', 'desc')           // order
            ->orderBy('week', 'desc')
            ->take($calculation->duration + 1)  // limit
            ->get()                             // fetch
            ->reverse();                        // oldest first

        // set the week pointer to first week we need to include in the report
        $latestDate = $this->getLatest()->first()->date;
        $monday = date('Y-m-d', strtotime($latestDate . ' Monday this Week -' . $calculation->duration . ' Weeks '));

        // load (potentially) needed exchange rates in a single query
        $currencies = [];
        if ($calculation->base != $this->dbBase) $currencies[] = $calculation->base;
        if ($calculation->target != $this->dbBase) $currencies[] = $calculation->target;
        $this->rates = CurrencyRate::where('date', '>=', $monday)
            ->whereIn('currency', $currencies)
            ->orderBy('date', 'asc')
            ->get();

        // iterate over weeks we need in the report
        while ($monday && strtotime($monday) < strtotime($latestDate)) {
            $this->calculateWeek($monday);

            // increment week pointer
            $monday = date('Y-m-d', strtotime($monday . ' +1 Week'));
        }

        // at this point, we should have at least as many weeks in the
        // collection as we need. let's just truncate it
        // (negative because we want the latest results)
        $this->weeks = $this->weeks->take(-$calculation->duration);

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
        foreach ($this->weeks as $week) {
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
            $week->profit = round($week->amount - $this->parameters->amount * $latestRate, 2);
        }

        // store results
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
     * Return query counters
     *
     * @return array
     */
    public function getCounters()
    {
        return [$this->apiQueries, $this->totalQueries];
    }

    /**
     * Get rates for the given date. Queries the API only if necessary.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRates($date)
    {
        $this->totalQueries++;

        $rates = $this->getRatesByDate($date);
        if (!$rates->isEmpty()) {
            return $rates;
        }

        // query via API
        return $this->query($date);
    }

    /**
     * Get latest rates. Queries the API only if necessary.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLatest()
    {
        if ($this->latest) {
            return $this->latest;
        }

        if (!$this->isApiQueryNeeded() || !$this->latest = $this->query()) {
            return $this->latest = $this->getRatesByDate($this->latestDate);
        } else {
            return $this->latest;
        }
    }

    /**
     * Determine if we need to make a query to the currency rate API.
     *
     * @return boolean
     */
    protected function isApiQueryNeeded()
    {
        if ($this->rates->isEmpty()) {
            $latest = CurrencyRate::orderBy('date', 'desc')->take(1)->get();
        } else {
            $latest = $this->rates->take(-1);
        }

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
     * @param string|null $date Omit to query for latest rates.
     * @return \Illuminate\Support\Collection
     */
    protected function query($date = null)
    {
        $this->apiQueries++;

        // run api query
        $api = \CurrencyRates::driver(config('app.currency_api'));
        if ($date) {
            $results = $api->historical(new \DateTime($date), 'EUR');
        } else {
            $results = $api->latest('EUR');
            $date = date('Y-m-d');
        }

        // verify that we got the results we asked for
        if ($results->date->format('Y-m-d') !== $date) {
            return null;
        }

        // store results in the db
        $rates = collect([]);
        foreach ($results->rates as $currency => $value) {
            $rate = new CurrencyRate;
            $rate->date = $results->date;
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
        if ($base === $this->dbBase) {
            $rate = $rates->first(function ($model) use ($target) {
                return $model->currency === $target;
            });

            return $rate->rate;
        }

        // if target is the same as database base, return inverted rate
        if ($target === $this->dbBase) {

            $rate = $rates->first(function ($model) use ($base) {
                return $model->currency === $base;
            });

            return round(1 / $rate->rate, 5);
        }

        // otherwise, rebase the rate
        $rateBase = $rates->first(function ($model) use ($base) {
            return $model->currency === $base;
        });
        $rateTarget = $rates->first(function ($model) use ($target) {
            return $model->currency === $target;
        });

        return round($rateTarget->rate / $rateBase->rate, 5);
    }

    /**
     * Returns collection of exchange rates for the given date.
     *
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    protected function getRatesByDate($date)
    {
        if ($this->rates->isEmpty()) {
            // no cache, query directly
            return CurrencyRate::where('date', $date)->get();
        }

        return $this->rates->filter(function ($value) use ($date) {
            return strtotime($value->date) == strtotime($date);
        });
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
        $year = date('Y', strtotime($monday));
        $week = date('W', strtotime($monday));

        // find out whether we need to add/update the week in question
        $existing = $this->weeks->first(function ($value) use ($year, $week) {
            return $value->year == $year && $value->week == $week;
        });
        if ($existing && $existing->complete) {
            // no need to (re)calculate
            return;
        }

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
            return;
        }

        // determine if this week has been completed
        $complete = strcmp($this->latestDate, $date) > 0 // not current week
                 || date('w', strtotime($date)) === 4;   // last day is friday

        // populate
        $data = [
            'year' => $year,
            'week' => $week,
            'rate' => $rate, // this will be set to the rate of the last recorded day
            'rate_min' => $rateMin,
            'rate_max' => $rateMax,
            'amount' => round($params->amount * $rate, 2),
            'complete' => $complete,
            'last_day' => $lastDay,
        ];

        // update existing week
        if ($existing) {
            $existing->fill($data);
            $existing->save();
            return;
        }

        // create new week
        $model = $params->weeks()->create($data);

        // determine whether we are prepending or appending to the collection
        $first = $this->weeks->first();
        if ($year < $first->year || $week < $first->week) {
            // prepend
            $this->weeks->prepend($model);
        } else {
            // append
            $this->weeks->push($model);
        }

    }
}
