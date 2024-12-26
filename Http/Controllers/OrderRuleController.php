<?php

namespace Amplify\System\OrderRule\Http\Controllers;

use Amplify\Frontend\Traits\HasDynamicPage;
use Amplify\System\OrderRule\Facades\OrderRuleCheck;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderRuleController extends Controller
{
    use HasDynamicPage;

    /**
     * Display a listing of the resource.
     *
     * @throws \ErrorException
     */
    public function index(): string
    {
        abort_unless(customer(true)->can('order-processing-rules.manage-rules'), 403);

        $this->loadPageByType('order_rule');

        return $this->render();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws \ErrorException
     */
    public function create(): string
    {
        abort_unless(customer(true)->can('order-processing-rules.manage-rules'), 403);

        push_css('https://unpkg.com/vue-multiselect@2.1.6/dist/vue-multiselect.min.css', 'custom-style');

        $this->loadPageByType('order_rule_create');

        return $this->render();
    }

    public function cartOrderRuleCheck(Request $request)
    {
        $orderStatus = OrderRuleCheck::cartCheck($request->ship_to_number);

        return response()->json([
            'status' => $orderStatus,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @throws \ErrorException
     */
    public function edit(string $id): string
    {
        abort_unless(customer(true)->can('order-processing-rules.manage-rules'), 403);
        push_css('https://unpkg.com/vue-multiselect@2.1.6/dist/vue-multiselect.min.css', 'custom-style');
        $this->loadPageByType('order_rule_edit');

        return $this->render();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
