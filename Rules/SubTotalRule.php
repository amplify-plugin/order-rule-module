<?php

namespace Amplify\System\OrderRule\Rules;

use Amplify\ErpApi\Collections\ShippingLocationCollection;
use Amplify\ErpApi\Facades\ErpApi;
use Amplify\System\OrderRule\Interfaces\OrderRuleInterface;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Traits\OrderRuleTrait;

class SubTotalRule implements OrderRuleInterface
{
    use OrderRuleTrait;

    private ShippingLocationCollection $shipAddresses;

    /**
     * @var null
     */
    private $order_ship_to_address;

    public function prepareForValidation(): void
    {
        $this->shipAddresses = ErpApi::getCustomerShippingLocationList(['customer_number' => $this->customer->customer_erp_id]);
    }

    /**
     * Attempt a rule on a customer order
     *
     * @return bool
     */
    public function attempt(CustomerOrder $order): array
    {
        if (! empty($this->value)) {

            $subTotalRules = array_filter($this->value, function ($ruleValue) use (&$order) {
                $ruleValue['to_amount'] = ($ruleValue['to_amount'] == null) ? PHP_INT_MAX : $ruleValue['to_amount'];
                $ruleValue['from_amount'] = ($ruleValue['from_amount'] == null) ? PHP_INT_MIN : $ruleValue['from_amount'];

                if (($order->total_net_price >= $ruleValue['from_amount']) && ($order->total_net_price <= $ruleValue['to_amount'])) {
                    if (empty($ruleValue['contacts'])) {
                        return true;
                    }
                    if (! empty($ruleValue['contacts']) && in_array($order->contact->id, $ruleValue['contacts'])) {
                        return true;
                    }

                    return false;

                }

                return false;
            });

            $this->order_ship_to_address = $this->getOrderShipToAddress($order);

            $shipAddressRules = array_filter($subTotalRules, function ($ruleValue) use (&$order) {

                if (empty($ruleValue['ship_to'])) {
                    return true;
                }
                if (! empty($ruleValue['ship_to']) && in_array($order->order_ship_to_address, $ruleValue['ship_to'])) {
                    return true;
                }

                return false;
            });

            // $isContactEligible = array_filter($this->value, function ($ruleValue) use (&$order) {
            //         if(in_array($order->contact->id, $ruleValue['contacts'])){
            //             return true;
            //         }
            //         return false;
            // });
            $rules = array_keys($shipAddressRules);

            foreach ($rules as $rule) {
                $this->notify($this->value[$rule]['approvers']);
            }
        }

        return $this->approves;
    }

    public function cartAttempt(Cart $cart, $shipToNumber): array
    {
        if (! empty($this->value)) {
            $this->order_ship_to_address = $shipToNumber;

            $subTotalRules = array_filter($this->value, function ($ruleValue) use ($cart) {
                $ruleValue['to_amount'] = ($ruleValue['to_amount'] == null) ? PHP_INT_MAX : $ruleValue['to_amount'];
                $ruleValue['from_amount'] = ($ruleValue['from_amount'] == null) ? PHP_INT_MIN : $ruleValue['from_amount'];

                if (($cart->total >= $ruleValue['from_amount']) && ($cart->total <= $ruleValue['to_amount'])) {
                    if (empty($ruleValue['contacts'])) {
                        return true;
                    }
                    if (! empty($ruleValue['contacts']) && in_array($cart->contact->id, $ruleValue['contacts'])) {
                        return true;
                    }

                    return false;

                }

                return false;
            });

            $shipAddressRules = array_filter($subTotalRules, function ($ruleValue) {
                if (empty($ruleValue['ship_to'])) {
                    return true;
                }
                if (! empty($ruleValue['ship_to']) && in_array($order->order_ship_to_address, $ruleValue['ship_to'])) {
                    return true;
                }

                return false;
            });

            return array_keys($shipAddressRules);
        }

        return [];
    }

    /**
     * @param  string  $orderAddressName
     * @param  array  $ruleValue
     */
    private function verifyShipAddress($orderAddressName, $ruleValue): bool
    {
        if ($orderAddressName == $ruleValue) {
            return true;
        }

        return false;
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
            $data['ship_addresses'] = $customer->addresses;
            $data['approvers'] = $customer->contacts;
        }

        return $data;
    }

    private function getOrderShipToAddress(CustomerOrder $order): string
    {
        $ShipToNumber = $order->erp_info->ShipToNumber ?? null;
        if ($ShipToNumber == null) {
            return '';
        }

        return $ShipToNumber;
    }
}
