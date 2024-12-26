<?php

namespace Amplify\System\OrderRule;

use Amplify\System\OrderRule\Models\CustomerOrderRule;
use Amplify\System\OrderRule\Models\CustomerOrderRuleTrack;
use App\Factories\NotificationFactory;
use App\Models\Cart;
use App\Models\CustomerOrder;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

class OrderRuleCheck
{
    /**
     * @var Collection
     */
    private $rules;

    public function __construct()
    {
        $this->rules = collect();
    }

    public function cartCheck($shipToNumber): string
    {
        $cart = getCart();
        $customer = customer();
        $this->rules = $customer->customerOrderRules;

        if ($this->rules->isEmpty()) {
            return 'passed';
        }

        foreach ($this->rules as $customerOrderRule) {
            $approvers = $this->attempt($cart, $customerOrderRule, $shipToNumber);

            if (! empty($approvers)) {
                return 'need_approver';
            }
        }

        return 'passed';
    }

    public function check(CustomerOrder $order): string
    {
        $customer = $order->customer;
        $approvalStatus = 'passed';

        $this->rules = $customer->customerOrderRules;

        if ($this->rules->isEmpty()) {
            return $approvalStatus;
        }

        foreach ($this->rules as $customerOrderRule) {
            $approvers = $this->attempt($order, $customerOrderRule);

            if (! empty($approvers)) {
                $this->processApproval($order, $customerOrderRule, $approvers);
                $approvalStatus = 'need_approver';
            }
        }

        return $approvalStatus;
    }

    private function attempt(CustomerOrder|Cart $cartOrOrder, CustomerOrderRule $customerOrderRule, $shipToNumber = null): array
    {

        if (isset($customerOrderRule->orderRule->target_rule)) {

            $orderRule = $customerOrderRule->orderRule->target_rule;

            $orderRule->setValue($customerOrderRule->value);

            $orderRule->setCustomer($customerOrderRule->customer);

            $orderRule->prepareForValidation();

            if ($cartOrOrder instanceof CustomerOrder) {
                return $orderRule->attempt($cartOrOrder);
            } else {
                return $orderRule->cartAttempt($cartOrOrder, $shipToNumber);
            }
        }

        return [];
    }

    private function processApproval(CustomerOrder $order, CustomerOrderRule $customerOrderRule, array $approvers): void
    {
        $this->createApprovalTracks($order, $customerOrderRule, $approvers);
    }

    private function createApprovalTracks(CustomerOrder $order, CustomerOrderRule $customerOrderRule, array $approvers): void
    {

        foreach ($approvers as $approver) {
            $rule_track = CustomerOrderRuleTrack::create([
                'customer_order_id' => $order->id,
                'order_rule_id' => $customerOrderRule->orderRule->id,
                'approver_id' => $approver,
            ]);

            $this->notifyApprover($rule_track);
        }
    }

    private function notifyApprover(CustomerOrderRuleTrack $rule_track): void
    {
        // This is for approver.
        NotificationFactory::call(Event::ORDER_RULE_CHECKED, [
            'contact_id' => $rule_track->approver_id,
            'rule_track_id' => $rule_track->id,
        ]);

        // This is for order's contact.
        NotificationFactory::call(Event::ORDER_WAITING_APPROVAL, [
            'contact_id' => $rule_track->customerOrder->contact_id,
            'rule_track_id' => $rule_track->id,
        ]);
    }
}
