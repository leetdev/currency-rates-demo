<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCalculationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|not_in:0|min:0',
            'base' => ['required', Rule::in(config('app.currencies'))],
            'target' => ['required', Rule::in(config('app.currencies')), 'different:base'],
            'duration' => [
                'required',
                'numeric',
                'min:' . config('app.min_duration'),
                'max:' . config('app.max_duration'),
            ],
        ];
    }
}
