<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = ['date', 'currency'];
    protected $dates = ['date'];
}
