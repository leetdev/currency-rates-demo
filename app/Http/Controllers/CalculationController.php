<?php

namespace App\Http\Controllers;

use App\Calculator;
use App\CurrencyCalculation;
use App\Http\Requests\StoreCalculationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalculationController extends Controller
{

    /**
     * Controller constructor
     */
    public function __construct()
    {
        // Middleware
        $this->middleware('auth');
    }

    /**
     * Display the favourite calculation and a listing of calculations.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // fetch all calculations
        $calculations = $user->currencyCalculations;

        // determine favorite calculation
        if ($calculations->isEmpty()) {
            return redirect('create')
                ->with('alert', 'info')
                ->with('flash', 'You have not set up any currency pairs yet. Please do so now!');
        } else {
            $favourite = $user->favouriteCalculation;
            if (!$favourite) {
                $favourite = $calculations->first();
            }
        }
        // render the page
        return view('index', [
            'calculations' => $calculations,
            'favourite' => $favourite,
        ]);
    }

    /**
     * Show the form for creating a new calculation.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // render
        $currencies = config('app.currencies');
        return view('calculations.create', ['currencies' => array_combine($currencies, $currencies)]);
    }

    /**
     * Store a newly created calculation in storage.
     *
     * @param  \App\Http\Requests\StoreCalculationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCalculationRequest $request)
    {
        $user = $request->user();
        $input = $request->all();

        // no further validation needed thanks to StoreCalculationRequest
        $calculation = $user->currencyCalculations()->create($input);

        // set as favourite if requested
        if (isset($input['favourite'])) {
            $user->favouriteCalculation()->associate($calculation);
            $user->save();
        }

        // done!
        return redirect('/')
            ->with('alert', 'success')
            ->with('flash', 'Currency pair has been successfully created.');
    }

    /**
     * Display the specified calculation.
     *
     * @param  \App\CurrencyCalculation  $calculation
     * @param  \App\Calculator           $calc
     * @return \Illuminate\Http\Response
     */
    public function show(CurrencyCalculation $calculation, Calculator $calc)
    {
        // make sure user owns this calculation
        $this->authorize('access', $calculation);

        // prepare calculation results
        $calc->prepare($calculation);

        // render results
        return view('calculations.show', [
            'parameters' => $calculation,
            'weeks' => $calc->weeks(),
            'chart' => $calc->chart(),
            'hilo' => $calc->hilo(),
        ]);
    }

    /**
     * Show the form for editing the specified calculation.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\CurrencyCalculation  $calculation
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, CurrencyCalculation $calculation)
    {
        // make sure user owns this calculation
        $this->authorize('access', $calculation);

        // prepare data
        $currencies = config('app.currencies');
        $favourite = $request->user()->favouriteCalculation;
        $favourite = $favourite && $calculation->id == $favourite->id;

        // render
        return view('calculations.edit', [
            'currencies' => array_combine($currencies, $currencies),
            'calculation' => $calculation,
            'favourite' => $favourite,
        ]);
    }

    /**
     * Update the specified calculation in storage.
     *
     * @param  \App\Http\Requests\StoreCalculationRequest $request
     * @param  \App\CurrencyCalculation  $calculation
     * @return \Illuminate\Http\Response
     */
    public function update(StoreCalculationRequest $request, CurrencyCalculation $calculation)
    {
        // make sure user owns this calculation
        $this->authorize('access', $calculation);

        $user = $request->user();
        $favourite = $user->favouriteCalculation;
        $input = $request->all();

        // no further validation needed thanks to StoreCalculationRequest
        $calculation->fill($input);
        $user->currencyCalculations()->save($calculation);

        // update favourite if requested
        if (isset($input['favourite'])) {
            $user->favouriteCalculation()->associate($calculation);
            $user->save();
        } elseif ($favourite && $favourite->id == $calculation->id) {
            $user->favouriteCalculation()->dissociate();
            $user->save();
        }

        // done!
        return redirect('/')
            ->with('alert', 'success')
            ->with('flash', 'Currency pair has been successfully updated.');
    }

    /**
     * Set the specified calculation as favourite.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\CurrencyCalculation  $calculation
     * @return \Illuminate\Http\Response
     */
    public function favourite(Request $request, CurrencyCalculation $calculation)
    {
        // make sure user owns this calculation
        $this->authorize('access', $calculation);

        // set as favourite
        $user = $request->user();
        $user->favouriteCalculation()->associate($calculation);
        $user->save();

        // redirect
        return redirect('/')
            ->with('alert', 'success')
            ->with('flash', 'Your favourite calculation has been successfully updated.');
    }

    /**
     * Remove the specified calculation from storage.
     *
     * @param  \App\CurrencyCalculation  $calculation
     * @return \Illuminate\Http\Response
     */
    public function destroy(CurrencyCalculation $calculation)
    {
        // make sure user owns this calculation
        $this->authorize('access', $calculation);

        // delete and redirect
        $calculation->delete();
        return redirect('/')
            ->with('alert', 'success')
            ->with('flash', 'Currency pair has been deleted.');
    }
}
