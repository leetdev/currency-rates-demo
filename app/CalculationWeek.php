<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculationWeek extends Model
{
    /**
     * Get the calculation that owns this week's data.
     */
    public function calculation()
    {
        return $this->belongsTo('App\CurrencyCalculation');
    }
}
