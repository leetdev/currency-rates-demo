<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Calculator;

class CacheCurrencyRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store currency rates for the maximum duration configured';

    /**
     * The calculator service.
     *
     * @var \App\Calculator
     */
    protected $calculator;

    /**
     * Create a new command instance.
     *
     * @param \App\Calculator $calculator
     * @return void
     */
    public function __construct(Calculator $calculator)
    {
        parent::__construct();

        $this->calculator = $calculator;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $latest = $this->calculator->getLatest();
        $latestDate = !$latest->isEmpty() ? $latest->first()->date : date('Y-m-d');
        $weeks = config('app.max_duration');
        $needsUpdate = date('Y-m-d', strtotime("$latestDate Monday this Week -$weeks Weeks "));

        $this->info("Populating data starting from $needsUpdate...");

        $bar = $this->output->createProgressBar($weeks);

        while ($needsUpdate && strtotime($needsUpdate) < strtotime($latestDate)) {
            // get rates for all working days of the week
            for ($i = 0; $i < 5; $i++) {
                $date = $i ? date('Y-m-d', strtotime("$needsUpdate +$i Days")) : $needsUpdate;
                if (strcmp($date, $latestDate) > 0) {
                    break;
                }

                $results = $this->calculator->getRates($date);
            }

            // increment week pointer
            $needsUpdate = date('Y-m-d', strtotime($needsUpdate . ' +1 Week'));

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        list($api, $total) = $this->calculator->getCounters();
        $this->info("$api/$total queries sent to API.");
    }
}
