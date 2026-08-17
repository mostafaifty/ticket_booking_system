<?php

namespace App\Http\Requests\Admin\Train;

use App\Models\Train;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTrainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Prepare inputs before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('train_number')) {
            $this->merge([
                'train_number' => trim($this->train_number),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'train_number' => ['required', 'string', 'max:20', 'unique:trains,train_number'],
            'train_name' => ['required', 'string', 'max:100'],
            'train_type' => ['required', Rule::in([
                Train::TYPE_INTERCITY,
                Train::TYPE_MAIL_EXPRESS,
                Train::TYPE_COMMUTER,
            ])],
            'total_seats' => ['required', 'integer', 'min:0', 'max:2000'],
            'status' => ['required', Rule::in([
                Train::STATUS_ACTIVE,
                Train::STATUS_INACTIVE,
                Train::STATUS_MAINTENANCE,
            ])],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'train_number.unique' => 'A train with this train number already exists.',
            'train_type.in' => 'Please select a valid train type.',
            'status.in' => 'Please select a valid operational status.',
            'total_seats.min' => 'Total seats cannot be negative.',
        ];
    }
}
