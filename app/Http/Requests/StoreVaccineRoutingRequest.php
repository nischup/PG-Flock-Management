<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccineRoutingRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'status' => 'required|in:1,0',
            'description' => 'nullable|string|max:1000',
        ];

        // For updates, ignore unique rule for the current record
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] .= '|unique:vaccine_routings,name,'.$this->route('vaccine_routing')->id;
        } else {
            $rules['name'] .= '|unique:vaccine_routings,name';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The route name is required.',
            'name.unique' => 'A route with this name already exists.',
            'name.max' => 'The route name may not be greater than 255 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The selected status is invalid.',
            'description.max' => 'The description may not be greater than 1000 characters.',
        ];
    }
}
