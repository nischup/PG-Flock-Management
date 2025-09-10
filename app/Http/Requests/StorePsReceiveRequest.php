<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePsReceiveRequest extends FormRequest
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
            // Master Information
            'shipment_type_id' => 'required|integer|in:1,2',
            'pi_no' => 'required|string|max:50|unique:ps_receives,pi_no',
            'pi_date' => 'required|date|before_or_equal:today',
            'order_no' => 'nullable|string|max:50',
            'order_date' => 'nullable|date|after_or_equal:pi_date',
            'lc_no' => 'nullable|string|max:50',
            'lc_date' => 'nullable|date|after_or_equal:pi_date',
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'breed_type' => 'required|array|min:1',
            'breed_type.*' => 'integer|exists:breed_types,id',
            'country_of_origin' => 'required|integer|exists:countries,id',
            'transport_type' => 'required|integer|exists:transport_types,id',
            'company_id' => 'required|integer|exists:companies,id',
            'vehicle_inside_temp' => 'nullable|numeric|between:-50,50',
            'remarks' => 'nullable|string|max:500',

            // Chick Counts
            'ps_male_rec_box' => 'required|numeric|min:0',
            'ps_male_qty' => 'required|numeric|min:0',
            'ps_female_rec_box' => 'required|numeric|min:0',
            'ps_female_qty' => 'required|numeric|min:0',
            'ps_total_qty' => 'required|numeric|min:0',
            'ps_total_re_box_qty' => 'required|numeric|min:0',
            'ps_challan_box_qty' => 'required|numeric|min:0',
            'ps_gross_weight' => 'required|numeric|min:0',
            'ps_net_weight' => 'required|numeric|min:0',

            // Lab Transfer
            'gov_lab_send_female_qty' => 'nullable|integer|min:0',
            'gov_lab_send_male_qty' => 'nullable|integer|min:0',
            'gov_lab_send_total_qty' => 'nullable|integer|min:0',
            'provita_lab_send_female_qty' => 'nullable|integer|min:0',
            'provita_lab_send_male_qty' => 'nullable|integer|min:0',
            'provita_lab_send_total_qty' => 'nullable|integer|min:0',

            // File Uploads
            'file.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // 10MB max
            'labfile.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
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
            'ps_male_rec_box' => $this->convertToNumeric($this->ps_male_rec_box),
            'ps_male_qty' => $this->convertToNumeric($this->ps_male_qty),
            'ps_female_rec_box' => $this->convertToNumeric($this->ps_female_rec_box),
            'ps_female_qty' => $this->convertToNumeric($this->ps_female_qty),
            'ps_total_qty' => $this->convertToNumeric($this->ps_total_qty),
            'ps_total_re_box_qty' => $this->convertToNumeric($this->ps_total_re_box_qty),
            'ps_challan_box_qty' => $this->convertToNumeric($this->ps_challan_box_qty),
            'ps_gross_weight' => $this->convertToNumeric($this->ps_gross_weight),
            'ps_net_weight' => $this->convertToNumeric($this->ps_net_weight),
            'ps_bonus_qty' => $this->convertToNumeric($this->ps_bonus_qty),
            'gov_lab_send_female_qty' => $this->convertToNumeric($this->gov_lab_send_female_qty),
            'gov_lab_send_male_qty' => $this->convertToNumeric($this->gov_lab_send_male_qty),
            'gov_lab_send_total_qty' => $this->convertToNumeric($this->gov_lab_send_total_qty),
            'provita_lab_send_female_qty' => $this->convertToNumeric($this->provita_lab_send_female_qty),
            'provita_lab_send_male_qty' => $this->convertToNumeric($this->provita_lab_send_male_qty),
            'provita_lab_send_total_qty' => $this->convertToNumeric($this->provita_lab_send_total_qty),
            'vehicle_inside_temp' => $this->vehicle_inside_temp ? $this->convertToNumeric($this->vehicle_inside_temp) : null,
        ]);

        // Handle breed_type conversion from objects to IDs
        if ($this->has('breed_type') && is_array($this->breed_type)) {
            $breedTypeIds = collect($this->breed_type)->map(function ($item) {
                return is_array($item) && isset($item['id']) ? (int) $item['id'] : (int) $item;
            })->filter()->unique()->values()->toArray();
            
            $this->merge(['breed_type' => $breedTypeIds]);
        }
    }

    /**
     * Convert value to numeric, handling empty strings and null values.
     *
     * @param mixed $value
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
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'shipment_type_id.required' => 'Shipment type is required.',
            'shipment_type_id.in' => 'Invalid shipment type selected.',
            'pi_no.required' => 'PI Number is required.',
            'pi_no.unique' => 'This PI Number already exists.',
            'pi_no.max' => 'PI Number cannot exceed 50 characters.',
            'pi_date.required' => 'PI Date is required.',
            'pi_date.before_or_equal' => 'PI Date cannot be in the future.',
            'order_date.after_or_equal' => 'Order date must be after or equal to PI date.',
            'lc_date.after_or_equal' => 'LC date must be after or equal to PI date.',
            'supplier_id.required' => 'Supplier selection is required.',
            'supplier_id.exists' => 'Selected supplier does not exist.',
            'breed_type.required' => 'At least one breed type must be selected.',
            'breed_type.min' => 'At least one breed type must be selected.',
            'breed_type.*.exists' => 'One or more selected breed types do not exist.',
            'country_of_origin.required' => 'Country of origin is required.',
            'country_of_origin.exists' => 'Selected country does not exist.',
            'transport_type.required' => 'Transport type is required.',
            'transport_type.exists' => 'Selected transport type does not exist.',
            'company_id.required' => 'Company selection is required.',
            'company_id.exists' => 'Selected company does not exist.',
            'vehicle_inside_temp.between' => 'Vehicle temperature must be between -50°C and 50°C.',
            'remarks.max' => 'Remarks cannot exceed 500 characters.',
            
            // Chick Counts Messages
            'ps_male_rec_box.required' => 'Male box quantity is required.',
            'ps_male_rec_box.min' => 'Male box quantity cannot be negative.',
            'ps_male_qty.required' => 'Male quantity is required.',
            'ps_male_qty.min' => 'Male quantity cannot be negative.',
            'ps_female_rec_box.required' => 'Female box quantity is required.',
            'ps_female_rec_box.min' => 'Female box quantity cannot be negative.',
            'ps_female_qty.required' => 'Female quantity is required.',
            'ps_female_qty.min' => 'Female quantity cannot be negative.',
            'ps_total_qty.required' => 'Total quantity is required.',
            'ps_total_qty.min' => 'Total quantity must be greater than 0.',
            'ps_total_re_box_qty.required' => 'Total box quantity is required.',
            'ps_total_re_box_qty.min' => 'Total box quantity cannot be negative.',
            'ps_challan_box_qty.required' => 'Challan box quantity is required.',
            'ps_challan_box_qty.min' => 'Challan box quantity cannot be negative.',
            'ps_gross_weight.required' => 'Gross weight is required.',
            'ps_gross_weight.min' => 'Gross weight cannot be negative.',
            'ps_net_weight.required' => 'Net weight is required.',
            'ps_net_weight.min' => 'Net weight cannot be negative.',
            'ps_net_weight.max' => 'Net weight cannot exceed gross weight.',
            
            // Lab Transfer Messages
            'gov_lab_send_female_qty.min' => 'Government lab female quantity cannot be negative.',
            'gov_lab_send_male_qty.min' => 'Government lab male quantity cannot be negative.',
            'gov_lab_send_total_qty.min' => 'Government lab total quantity cannot be negative.',
            'provita_lab_send_female_qty.min' => 'Provita lab female quantity cannot be negative.',
            'provita_lab_send_male_qty.min' => 'Provita lab male quantity cannot be negative.',
            'provita_lab_send_total_qty.min' => 'Provita lab total quantity cannot be negative.',
            
            // File Upload Messages
            'file.*.mimes' => 'Only PDF, JPG, JPEG, PNG, DOC, and DOCX files are allowed.',
            'file.*.max' => 'File size cannot exceed 10MB.',
            'labfile.*.mimes' => 'Only PDF, JPG, JPEG, PNG, DOC, and DOCX files are allowed.',
            'labfile.*.max' => 'File size cannot exceed 10MB.',
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
            // Custom validation for lab quantities
            $govTotal = $this->input('gov_lab_send_female_qty', 0) + $this->input('gov_lab_send_male_qty', 0);
            $provitaTotal = $this->input('provita_lab_send_female_qty', 0) + $this->input('provita_lab_send_male_qty', 0);
            
            if ($govTotal <= 0 && $provitaTotal <= 0) {
                $validator->errors()->add('lab_quantities', 'At least one lab quantity must be provided.');
            }
            
            // Custom validation for quantity consistency
            $maleQty = (float) $this->input('ps_male_qty', 0);
            $femaleQty = (float) $this->input('ps_female_qty', 0);
            $totalQty = (float) $this->input('ps_total_qty', 0);
            
            if (($maleQty + $femaleQty) != $totalQty) {
                $validator->errors()->add('ps_total_qty', 'Total quantity must equal male + female quantity.');
            }
            
            // Custom validation for weight consistency
            $grossWeight = (float) $this->input('ps_gross_weight', 0);
            $netWeight = (float) $this->input('ps_net_weight', 0);
            
            if ($netWeight > $grossWeight) {
                $validator->errors()->add('ps_net_weight', 'Net weight cannot exceed gross weight.');
            }
        });
    }
}
