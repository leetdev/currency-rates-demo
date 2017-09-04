<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyCalculation extends Model
{
    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'amount',
        'base',
        'target',
        'duration',
    ];

    /**
     * Get the user that owns this calculation.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
