<?php

namespace Amplify\System\OrderRule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|min:5|max:255',
            'order_rule_id' => 'required',
            'customer_id' => 'required|integer',
            'value' => 'required|array',
        ];

        return $rules;
    }
}
