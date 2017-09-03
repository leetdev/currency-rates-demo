<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyCalculation extends Model
{

    /**
     * Get the user that owns this calculation.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
