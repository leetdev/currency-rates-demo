<?php

namespace App\Policies;

use App\User;
use App\CurrencyCalculation;
use Illuminate\Auth\Access\HandlesAuthorization;

class CalculationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the calculation.
     *
     * @param  \App\User  $user
     * @param  \App\CurrencyCalculation  $calculation
     * @return mixed
     */
    public function access(User $user, CurrencyCalculation $calculation)
    {
        return $user->id === $calculation->user_id;
    }
}
