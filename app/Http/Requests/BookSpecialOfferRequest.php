<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookSpecialOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'pickup_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'pickup_location' => ['required', 'string', 'max:500'],
            'drop_off_location' => ['nullable', 'string', 'max:500'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['required', 'string', 'max:50', 'regex:/^[0-9+()\-.\s]{6,50}$/'],
            'additional_note' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pickup_date.after_or_equal' => 'Please choose today or a later date.',
            'mobile.regex' => 'Please enter a valid phone number.',
            'terms.accepted' => 'Please accept the terms to continue.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pickup_date' => 'pickup date',
            'pickup_time' => 'pickup time',
            'pickup_location' => 'pickup address',
            'drop_off_location' => 'drop-off address',
            'full_name' => 'full name',
            'mobile' => 'phone number',
        ];
    }
}
