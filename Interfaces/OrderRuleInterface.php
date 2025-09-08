<?php

namespace Amplify\System\OrderRule\Interfaces;

use Amplify\System\Backend\Models\Customer;
use Amplify\System\Backend\Models\CustomerOrder;

interface OrderRuleInterface
{
    /**
     * Load the Customer associate with that order
     */
    public function setCustomer(Customer $customer): void;

    /**
     * push customer defined parameters to order rule class
     *
     * @param  mixed  $value
     */
    public function setValue($value = []): void;

    /**
     * Attempt a rule on a customer order
     *
     * @return bool
     */
    public function attempt(CustomerOrder $order): array;

    /**
     * Get Failed message for this rule
     */
    public function message(): string;

    /**
     * send a notification to approve user
     *
     * @param  array|int  $notifiable
     */
    public function notify($notifiable): void;

    /**
     * return all meta data required for this rule
     */
    public function meta(array $arguments = []): array;

    /**
     * load all preparation task before testing
     * the customer order
     */
    public function prepareForValidation(): void;
}
