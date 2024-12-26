<?php

namespace Amplify\System\OrderRule\Rules;

use Amplify\System\OrderRule\Interfaces\OrderRuleInterface;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use App\Traits\OrderRuleTrait;

class ForbiddenProductRule implements OrderRuleInterface
{
    use OrderRuleTrait;

    public function __construct() {}

    /**
     * Attempt a rule on a customer order
     *
     * @return bool
     */
    public function attempt(CustomerOrder $order): array
    {
        $product_id = $order->orderLines->pluck('product_id');
        $rules = collect($this->value);

        $matched_rule = $rules->whereIn('product', $product_id)->pluck('approvers')->toArray();
        $approvers = call_user_func_array('array_merge', $matched_rule);

        return $approvers;
    }

    /**
     * Get Failed message for this rule
     */
    public function message(): string
    {
        return '';
    }

    /**
     * return all meta data required for this rule
     */
    public function meta(array $arguments = []): array
    {
        $data = [];

        if ($customer_id = $arguments['customer_id']) {
            $customer = Customer::find($customer_id);
            $data['approvers'] = $customer->contacts;
        }
        $data['products'] = Product::select(['id', 'product_name'])->get();

        return $data;
    }
}
