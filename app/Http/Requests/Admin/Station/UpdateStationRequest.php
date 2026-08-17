<?php

namespace App\Http\Requests\Admin\Station;

use App\Models\Station;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Prepare inputs before validation (e.g. uppercase station code).
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code)),
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
        $stationId = $this->route('station') instanceof Station
            ? $this->route('station')->id
            : $this->route('station');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:10',
                'alpha_num',
                Rule::unique('stations', 'code')->ignore($stationId),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([Station::STATUS_ACTIVE, Station::STATUS_INACTIVE])],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'A station with this station code already exists.',
            'code.alpha_num' => 'The station code may only contain letters and numbers.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
}
