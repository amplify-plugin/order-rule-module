<?php

namespace Amplify\System\OrderRule\Http\Controllers;

use Amplify\ErpApi\Facades\ErpApi;
use Amplify\Frontend\Traits\HasDynamicPage;
use Amplify\System\Backend\Models\Event;
use Amplify\System\OrderRule\Models\CustomerOrderRuleTrack;
use App\Factories\NotificationFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderAwaitingApprovalController extends Controller
{
    use HasDynamicPage;

    /**
     * Display a listing of the resource.
     *
     * @throws \ErrorException
     */
    public function index(): string
    {
        $this->loadPageByType('order_awaiting_approval');

        return $this->render();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->loadPageByType('order_awaiting_approval_detail');

        return $this->render();
    }

    /**
     * Display the specified resource.
     */
    public function confirm(string $id)
    {
        $this->loadPageByType('order_checkout_confirm');

        return $this->render();
    }

    public function action(Request $request)
    {
        $order_rule = CustomerOrderRuleTrack::where('id', $request->id)
            ->where('approver_id', customer(true)->id)
            ->first();

        if ($order_rule) {
            $order_rule->update([
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            $erp_data = ErpApi::getCustomerDetail([
                'customer_number' => $order_rule->customerOrder->customer->customer_code,
            ]);

            // ERP order creatre
            if (($erp_data->CreditCardOnly == 'N' || $erp_data->CreditCardOnly === null) && $request->status == 'approved') {
                $order_rule->customerOrder()->update([
                    'approval_status' => $request->status,
                    'order_status' => 'Pending',
                ]);
                $apiResponse = $this->createOrderERP($order_rule->customer_order_id, $erp_data);

                if (! $apiResponse['success']) {
                    return response([
                        'message' => 'ERP Order Submission Failed',
                    ], 500);
                }

                NotificationFactory::call(Event::ORDER_REQUEST_APPROVED, [
                    'contact_id' => $order_rule->customerOrder->contact_id,
                    'order_rule_id' => $order_rule->id,
                ]);

            } elseif ($request->status == 'rejected') {
                $order_rule->customerOrder()->update([
                    'approval_status' => $request->status,
                    'order_status' => ucfirst($request->status),
                ]);

                NotificationFactory::call(Event::ORDER_REQUEST_REJECTED, [
                    'contact_id' => $order_rule->customerOrder->contact_id,
                    'order_rule_id' => $order_rule->id,
                ]);
            }

            return response([
                'message' => 'Order Is '.ucfirst($request->status),
            ]);
        }
    }

    public function createOrderERP(int $orderID, $customerDetails)
    {
        $customerOrder = \Amplify\System\Backend\Models\CustomerOrder::find($orderID);

        return $customerOrder->createOrderOrQuoteERP([
            'order_type' => 'O',
            'customer_email' => $customerOrder->contact->email,
            'customer_phone' => $customerDetails->CustomerPhone,
            'shipping_number' => $customerOrder->shipping_number,
        ] + (array) $customerOrder->temp_address);
    }
}
