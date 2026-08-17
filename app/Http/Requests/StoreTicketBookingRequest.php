<?php

namespace App\Http\Requests;

use App\Models\Passenger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketBookingRequest extends FormRequest
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
            'passenger_name' => ['required', 'string', 'min:3', 'max:100'],
            'passenger_phone' => ['required', 'string', 'min:7', 'max:25'],
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
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'seat_id.required' => 'Please select a seat from the coach map.',
            'seat_id.exists' => 'The selected seat does not exist.',
            'passenger_name.required' => 'Passenger full name is required.',
            'passenger_name.min' => 'Passenger name must be at least 3 characters.',
            'passenger_phone.required' => 'Contact phone number is required.',
            'passenger_phone.min' => 'Phone number must be at least 7 digits.',
            'gender.in' => 'Please select a valid gender option.',
            'age.min' => 'Age must be a positive integer.',
        ];
    }
}
