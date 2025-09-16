<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlockRequest extends FormRequest
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
            'name' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('flocks', 'name'),
            ],
            'parent_flock_id' => 'nullable|exists:flocks,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Flock No is required.',
            'name.integer' => 'Flock No must be a valid integer.',
            'name.min' => 'Flock No must be at least 1.',
            'name.unique' => 'Flock No already exists.',
            'parent_flock_id.exists' => 'Selected parent flock does not exist.',
        ];
    }
}
