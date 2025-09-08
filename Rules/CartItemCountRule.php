<?php

namespace Amplify\System\OrderRule\Rules;

use Amplify\System\Backend\Models\Customer;
use Amplify\System\Backend\Models\CustomerOrder;
use Amplify\System\OrderRule\Interfaces\OrderRuleInterface;
use Amplify\System\OrderRule\Traits\OrderRuleTrait;

class CartItemCountRule implements OrderRuleInterface
{
    use OrderRuleTrait;

    public function __construct() {}

    /**
     * Attempt a rule on a customer order
     */
    public function attempt(CustomerOrder $order): array
    {
        return [];
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

        return $data;
    }
}
