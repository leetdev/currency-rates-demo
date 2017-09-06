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
    protected $parameters;
    protected $weeks;
    protected $chartData;
    protected $latestDate;

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
        $needsUpdate = true;
        $latest = null;
        if ($weeks->count() > 0) {
            $latest = $weeks->last();
            if ($latest->complete && $latest->week == $weekNumber) {
                $needsUpdate = false;
            }
        }

        // update results
        if ($needsUpdate) {
            $rates = $this->getLatest();

            // update incomplete last week if we can
            if ($latest && !$latest->complete && $latest->lastDay !== $today) {

            }

            if (!$weeks->count()) {
                // no cache, generate from scratch

            } else {
                //
            }

            //
        }

        // prepare chart data

        // populate chart data
        foreach ($weeks as $week) {

        }

        // store results
        $this->weeks = $weeks;
    }

    /**
     * Provides a collection of CalculationWeek model objects.
     * Use when providing data for the view that renders the results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function weeks()
    {
        return $weeks;
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
                return $rates;
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
        if (!$this->isApiQueryNeeded()) {
            return CurrencyRate::where('date', $this->latestDate)->get();
        }

        // query the API for latest rates
        return $this->query();
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
        $this->latestDate = $latest->date;

        // today's rates already in the db
        if ($latest->date == date('Y-m-d')) {
            return false;
        }

        // latest weekday result already in the db
        if (strtotime($latest->date . ' +1 Weekday') > time()) {
            return false;
        }

        // not enough time passed since last check
        if (Cache::get('currency.apicheck')) {
            return false;
        }

        // if we reached here, we need to query the api
        Cache::put('currency.apicheck', 'true', config('currency_apiquery_cache_expire_time'));
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
        //

        // store results in the db

        // return rates collection

    }
}
