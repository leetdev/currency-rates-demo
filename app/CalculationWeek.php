<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculationWeek extends Model
{
    public $timestamps = false;

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'year',
        'week',
        'rate',
        'rate_min',
        'rate_max',
        'amount',
        'complete',
        'last_day',
    ];

    /**
     * Get the calculation that owns this week's data.
     */
    public function calculation()
    {
        return $this->belongsTo('App\CurrencyCalculation');
    }
}
