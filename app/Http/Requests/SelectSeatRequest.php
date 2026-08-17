<?php

namespace App\Http\Requests;

use App\Models\Passenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectSeatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seat_id' => ['required', 'exists:seats,id'],
            'passenger_name' => ['required', 'string', 'max:100'],
            'passenger_phone' => ['required', 'string', 'max:25'],
            'nid_or_passport' => ['nullable', 'string', 'max:50'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
            'gender' => [
                'required',
                Rule::in([
                    Passenger::GENDER_MALE,
                    Passenger::GENDER_FEMALE,
                    Passenger::GENDER_OTHER,
                ]),
            ],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'seat_id.required' => 'Please select a seat on the train before proceeding.',
            'passenger_name.required' => 'Passenger full name is required.',
            'passenger_phone.required' => 'Contact phone number is required.',
        ];
    }
}
