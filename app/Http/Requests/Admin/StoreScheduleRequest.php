<?php

namespace App\Http\Requests\Admin;

use App\Models\TrainSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'train_id' => ['required', 'exists:trains,id'],
            'departure_station_id' => ['required', 'exists:stations,id'],
            'arrival_station_id' => [
                'required',
                'exists:stations,id',
                'different:departure_station_id',
            ],
            'departure_time' => ['required', 'date_format:H:i'],
            'arrival_time' => ['required', 'date_format:H:i'],
            'journey_date' => ['required', 'date', 'after_or_equal:today'],
            'fare' => ['required', 'numeric', 'min:0'],
            'status' => [
                'required',
                Rule::in([
                    TrainSchedule::STATUS_SCHEDULED,
                    TrainSchedule::STATUS_DELAYED,
                    TrainSchedule::STATUS_DEPARTED,
                    TrainSchedule::STATUS_COMPLETED,
                    TrainSchedule::STATUS_CANCELLED,
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
            'arrival_station_id.different' => 'The arrival station must be different from the departure station.',
            'journey_date.after_or_equal' => 'The journey date cannot be in the past.',
            'fare.min' => 'The fare amount must be a positive number.',
            'departure_time.date_format' => 'Departure time must be in HH:MM 24-hour format.',
            'arrival_time.date_format' => 'Arrival time must be in HH:MM 24-hour format.',
        ];
    }
}
