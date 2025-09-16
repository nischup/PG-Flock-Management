<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShedReceiveRequest extends FormRequest
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
            'transaction_id' => 'required|integer|exists:ps_firm_receives,id',
            'flock_id' => 'required|integer|exists:flocks,id',
            'shed_id' => 'required|integer|exists:sheds,id',
            'receiving_company_id' => 'required|integer|exists:companies,id',
            'shed_female_qty' => 'required|numeric|min:0',
            'shed_male_qty' => 'required|numeric|min:0',
            'shed_total_qty' => 'required|numeric|min:0',
            'shed_sortage_male_box' => 'nullable|numeric|min:0',
            'shed_sortage_female_box' => 'nullable|numeric|min:0',
            'shed_sortage_male_mortality' => 'nullable|numeric|min:0',
            'shed_sortage_female_mortality' => 'nullable|numeric|min:0',
            'shed_sortage_mortality' => 'nullable|numeric|min:0',
            'shed_sortage_box_qty' => 'nullable|numeric|min:0',
            'shed_excess_male_box' => 'nullable|numeric|min:0',
            'shed_excess_female_box' => 'nullable|numeric|min:0',
            'shed_excess_male_mortality' => 'nullable|numeric|min:0',
            'shed_excess_female_mortality' => 'nullable|numeric|min:0',
            'shed_excess_mortality' => 'nullable|numeric|min:0',
            'shed_excess_box_qty' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'transaction_id.required' => 'Firm receive selection is required.',
            'transaction_id.exists' => 'Selected firm receive does not exist.',
            'flock_id.required' => 'Flock selection is required.',
            'flock_id.exists' => 'Selected flock does not exist.',
            'shed_id.required' => 'Shed selection is required.',
            'shed_id.exists' => 'Selected shed does not exist.',
            'receiving_company_id.required' => 'Company selection is required.',
            'receiving_company_id.exists' => 'Selected company does not exist.',
            'shed_female_qty.required' => 'Female box quantity is required.',
            'shed_female_qty.min' => 'Female box quantity cannot be negative.',
            'shed_male_qty.required' => 'Male box quantity is required.',
            'shed_male_qty.min' => 'Male box quantity cannot be negative.',
            'shed_total_qty.required' => 'Total box quantity is required.',
            'shed_total_qty.min' => 'Total box quantity cannot be negative.',
            'shed_sortage_male_box.min' => 'Male shortage quantity cannot be negative.',
            'shed_sortage_female_box.min' => 'Female shortage quantity cannot be negative.',
            'shed_sortage_male_mortality.min' => 'Male shortage mortality cannot be negative.',
            'shed_sortage_female_mortality.min' => 'Female shortage mortality cannot be negative.',
            'shed_sortage_mortality.min' => 'Total shortage mortality cannot be negative.',
            'shed_sortage_box_qty.min' => 'Total shortage quantity cannot be negative.',
            'shed_excess_male_box.min' => 'Male excess quantity cannot be negative.',
            'shed_excess_female_box.min' => 'Female excess quantity cannot be negative.',
            'shed_excess_male_mortality.min' => 'Male excess mortality cannot be negative.',
            'shed_excess_female_mortality.min' => 'Female excess mortality cannot be negative.',
            'shed_excess_mortality.min' => 'Total excess mortality cannot be negative.',
            'shed_excess_box_qty.min' => 'Total excess quantity cannot be negative.',
            'remarks.max' => 'Remarks cannot exceed 500 characters.',
            'status.in' => 'Invalid status value.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation for quantity consistency
            $maleQty = (float) $this->input('shed_male_qty', 0);
            $femaleQty = (float) $this->input('shed_female_qty', 0);
            $totalQty = (float) $this->input('shed_total_qty', 0);
            
            if (($maleQty + $femaleQty) != $totalQty) {
                $validator->errors()->add('shed_total_qty', 'Total quantity must equal male + female quantity.');
            }
        });
    }
}