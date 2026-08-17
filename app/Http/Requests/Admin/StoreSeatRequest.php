<?php

namespace App\Http\Requests\Admin;

use App\Models\Seat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSeatRequest extends FormRequest
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
        $trainId = $this->route('train') ? $this->route('train')->id : $this->input('train_id');

        return [
            'coach' => ['required', 'string', 'max:10'],
            'seat_number' => [
                'required',
                'string',
                'max:10',
                Rule::unique('seats')->where(function ($query) use ($trainId) {
                    return $query->where('train_id', $trainId)
                        ->where('coach', $this->input('coach'));
                }),
            ],
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
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'seat_number.unique' => 'A seat with this number already exists in the selected coach for this train.',
        ];
    }
}
