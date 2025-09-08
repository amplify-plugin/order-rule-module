<?php

namespace Amplify\System\OrderRule\Http\Controllers;

use Amplify\System\Backend\Models\Contact;
use Amplify\System\OrderRule\Http\Requests\CustomerOrderRuleRequest;
use Amplify\System\OrderRule\Models\CustomerOrderRule;
use Amplify\System\OrderRule\Models\OrderRule;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CustomerOrderRuleController extends Controller
{
    /**
     * fetchRule
     *
     * @param  mixed  $request
     */
    public function fetchAvailableRule(Request $request): array
    {

        $queryBuilder = ($request->input('method') == 'put') ? 'whereIn' : 'whereNotIn';

        return OrderRule::$queryBuilder('id', function ($query) use ($request) {
            $query->select('order_rule_id')
                ->from('customer_order_rules')->where(['customer_id' => $request->customer_id]);
        })->get()->toArray();
    }

    /**
     * etchAvailableRule
     *
     * @param  mixed  $request
     */
    public function fetchRuleField(Request $request): JsonResponse
    {
        $customerOrderRule = CustomerOrderRule::where(['order_rule_id' => $request->order_rule_id, 'customer_id' => $request->customer_id])->first();
        $orderRule = OrderRule::find($request->order_rule_id);

        $metaData = $orderRule->target_rule->meta($request->all());

        return response()->json([
            'data' => $customerOrderRule ? $customerOrderRule->value : null,
            'meta' => $metaData,
        ]);
    }

    public function fetchContactsByCustomer($customer_id): JsonResponse
    {
        $contacts = Contact::whereCustomerId($customer_id)->get();

        return response()->json($contacts ?? []);
    }

    /**
     * creataOrUpdate order rule
     *
     * @param  mixed  $request
     * @return void
     *
     * @throws ValidationException
     */
    public function creataOrUpdate(CustomerOrderRuleRequest $request)
    {
        abort_unless(customer(true)->can('order-processing-rules.manage-rules') || customer(true)->can('order-processing-rules.manage-rules'), 403);
        try {
            $input = $request->all();
            unset($input['id']);

            if (isset($request->id) && ! empty($request->id)) {
                $order_rule = CustomerOrderRule::where('id', $request->id)->where('customer_id', customer()->id)->first();
                $order_rule->update($input);

                return response()->json(['message' => 'Update Successfully!', 'redirect_url' => 'order-rules'], 200);
            } else {
                CustomerOrderRule::create($input);

                return response()->json(['message' => 'Create Successfully!', 'redirect_url' => 'order-rules'], 200);
            }
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 500);
        }
    }

    /**
     * destroy customer order rule
     *
     * @param  mixed  $rule
     * @return void
     */
    public function destroy($rule)
    {
        try {
            $order_rule = CustomerOrderRule::where('id', $rule)->where('customer_id', customer()->id)->first();
            $order_rule->delete();
            Session::flash('success', 'You have successfully deleted the order rule!');
        } catch (\Throwable $th) {
            Session::flash('error', 'Sorry! Something went wrong...');
        }

        return back();
    }
}
