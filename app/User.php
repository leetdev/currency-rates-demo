<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'avatar', 'provider', 'provider_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token'
    ];

    /**
     * Get the favourite calculation for this user.
     */
    public function favouriteCalculation()
    {
        return $this->belongsTo('App\CurrencyCalculation');
    }

    /**
     * Get currency calculations for this user.
     */
    public function currencyCalculations()
    {
        return $this->hasMany('App\CurrencyCalculation');
    }
}
