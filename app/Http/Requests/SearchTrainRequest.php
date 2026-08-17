<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTrainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'departure_station_id' => ['nullable', 'exists:stations,id'],
            'arrival_station_id' => [
                'nullable',
                'exists:stations,id',
                'different:departure_station_id',
            ],
            'journey_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [
            'arrival_station_id.different' => 'From Station and To Station cannot be the same.',
            'departure_station_id.exists' => 'The selected departure station is invalid.',
            'arrival_station_id.exists' => 'The selected arrival station is invalid.',
            'journey_date.date' => 'Please provide a valid journey date.',
        ];
    }
}
