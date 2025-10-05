<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePsFirmReceiveRequest extends FormRequest
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
            'ps_receive_id' => 'required|integer|exists:ps_receives,id',
            'flock_id' => 'required|integer|exists:flocks,id',
            'receiving_company_id' => 'required|integer|exists:companies,id',
            'receiving_project_id' => 'required|integer|exists:projects,id',
            'firm_female_box_qty' => 'required|numeric|min:0',
            'firm_male_box_qty' => 'required|numeric|min:0',
            'firm_total_box_qty' => 'required|numeric|min:0',
            'firm_sortage_male_box' => 'nullable|numeric|min:0',
            'firm_sortage_female_box' => 'nullable|numeric|min:0',
            'firm_sortage_box_qty' => 'nullable|numeric|min:0',
            'firm_excess_male_box' => 'nullable|numeric|min:0',
            'firm_excess_female_box' => 'nullable|numeric|min:0',
            'firm_excess_box_qty' => 'nullable|numeric|min:0',
            'firm_mortality_female' => 'nullable|numeric|min:0',
            'firm_mortality_male' => 'nullable|numeric|min:0',
            'firm_total_mortality' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
            'status' => 'nullable|integer|in:0,1',
            'send_female_qty' => 'nullable|numeric|min:0',
            'send_male_qty' => 'nullable|numeric|min:0',
            'send_total_qty' => 'nullable|numeric|min:0',
            'lab_type' => 'nullable|integer|in:1,2',
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
            'ps_receive_id.required' => 'PS Receive selection is required.',
            'ps_receive_id.exists' => 'Selected PS Receive does not exist.',
            'flock_id.required' => 'Flock selection is required.',
            'flock_id.exists' => 'Selected flock does not exist.',
            'receiving_company_id.required' => 'Receiving company selection is required.',
            'receiving_company_id.exists' => 'Selected receiving company does not exist.',
            'receiving_project_id.required' => 'Project selection is required.',
            'receiving_project_id.exists' => 'Selected project does not exist.',
            'firm_female_box_qty.required' => 'Female box quantity is required.',
            'firm_female_box_qty.min' => 'Female box quantity cannot be negative.',
            'firm_male_box_qty.required' => 'Male box quantity is required.',
            'firm_male_box_qty.min' => 'Male box quantity cannot be negative.',
            'firm_total_box_qty.required' => 'Total box quantity is required.',
            'firm_total_box_qty.min' => 'Total box quantity cannot be negative.',
            'firm_sortage_male_box.min' => 'Shortage male box quantity cannot be negative.',
            'firm_sortage_female_box.min' => 'Shortage female box quantity cannot be negative.',
            'firm_sortage_box_qty.min' => 'Total shortage box quantity cannot be negative.',
            'firm_excess_male_box.min' => 'Excess male box quantity cannot be negative.',
            'firm_excess_female_box.min' => 'Excess female box quantity cannot be negative.',
            'firm_excess_box_qty.min' => 'Total excess box quantity cannot be negative.',
            'firm_mortality_female.min' => 'Female mortality quantity cannot be negative.',
            'firm_mortality_male.min' => 'Male mortality quantity cannot be negative.',
            'firm_total_mortality.min' => 'Total mortality quantity cannot be negative.',
            'remarks.max' => 'Remarks cannot exceed 500 characters.',
            'status.in' => 'Invalid status selected.',
            'send_female_qty.min' => 'Send female quantity cannot be negative.',
            'send_male_qty.min' => 'Send male quantity cannot be negative.',
            'send_total_qty.min' => 'Send total quantity cannot be negative.',
            'lab_type.in' => 'Invalid lab type selected.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert string values to proper numeric types
        $this->merge([
            'firm_female_box_qty' => $this->convertToNumeric($this->firm_female_box_qty),
            'firm_male_box_qty' => $this->convertToNumeric($this->firm_male_box_qty),
            'firm_total_box_qty' => $this->convertToNumeric($this->firm_total_box_qty),
            'firm_sortage_male_box' => $this->convertToNumeric($this->firm_sortage_male_box),
            'firm_sortage_female_box' => $this->convertToNumeric($this->firm_sortage_female_box),
            'firm_sortage_box_qty' => $this->convertToNumeric($this->firm_sortage_box_qty),
            'firm_excess_male_box' => $this->convertToNumeric($this->firm_excess_male_box),
            'firm_excess_female_box' => $this->convertToNumeric($this->firm_excess_female_box),
            'firm_excess_box_qty' => $this->convertToNumeric($this->firm_excess_box_qty),
            'firm_mortality_female' => $this->convertToNumeric($this->firm_mortality_female),
            'firm_mortality_male' => $this->convertToNumeric($this->firm_mortality_male),
            'firm_total_mortality' => $this->convertToNumeric($this->firm_total_mortality),
            'send_female_qty' => $this->convertToNumeric($this->send_female_qty),
            'send_male_qty' => $this->convertToNumeric($this->send_male_qty),
            'send_total_qty' => $this->convertToNumeric($this->send_total_qty),
        ]);
    }

    /**
     * Convert value to numeric, handling empty strings and null values.
     *
     * @param  mixed  $value
     * @return float|int
     */
    private function convertToNumeric($value)
    {
        if ($value === null || $value === '' || $value === 'null') {
            return 0;
        }

        return is_numeric($value) ? (float) $value : 0;
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
            $maleQty = (float) $this->input('firm_male_box_qty', 0);
            $femaleQty = (float) $this->input('firm_female_box_qty', 0);
            $totalQty = (float) $this->input('firm_total_box_qty', 0);

            if (($maleQty + $femaleQty) != $totalQty) {
                $validator->errors()->add('firm_total_box_qty', 'Total box quantity must equal male + female box quantity.');
            }
        });
    }
}
