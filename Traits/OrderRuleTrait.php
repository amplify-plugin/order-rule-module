<?php

namespace Amplify\System\OrderRule\Traits;

use Amplify\System\Backend\Models\Customer;

trait OrderRuleTrait
{
    public array $value;

    public Customer $customer;

    private array $approves;

    public function __construct()
    {
        $this->approves = [];
    }

    /**
     * @param  array  $value
     */
    public function setValue($value = []): void
    {
        $this->value = $value;
    }

    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * send a notification to approve user
     *
     * @param  array|int  $notifiable
     */
    public function notify($notifiable): void
    {
        if (is_array($notifiable)) {
            $this->approves = array_merge($this->approves, $notifiable);
        } else {
            array_push($this->approves, $notifiable);
        }

        $this->approves = array_unique($this->approves);
    }

    /**
     * load all preparation task before testing
     * the customer order
     */
    public function prepareForValidation(): void {}
}
