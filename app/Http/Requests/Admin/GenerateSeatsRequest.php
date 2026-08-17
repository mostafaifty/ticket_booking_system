<?php

namespace App\Http\Requests\Admin;

use App\Models\Seat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateSeatsRequest extends FormRequest
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
            'coach' => ['required', 'string', 'max:10'],
            'seat_class' => [
                'required',
                Rule::in([
                    Seat::CLASS_AC_BERTH,
                    Seat::CLASS_SNIGDHA,
                    Seat::CLASS_SHOVON_CHAIR,
                    Seat::CLASS_SHOVON,
                    Seat::CLASS_FIRST_CLASS,
                ]),
            ],
            'seat_count' => ['required', 'integer', 'min:1', 'max:100'],
            'start_number' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
