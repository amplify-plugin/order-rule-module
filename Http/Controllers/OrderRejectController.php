<?php

namespace Amplify\System\OrderRule\Http\Controllers;

use Amplify\Frontend\Traits\HasDynamicPage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderRejectController extends Controller
{
    use HasDynamicPage;

    /**
     * Display a listing of the resource.
     *
     * @throws \ErrorException
     */
    public function index(): string
    {
        abort_unless(customer(true)->can('order-rejected.list'), 403);

        $this->loadPageByType('order_rejected');

        return $this->render();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @throws \ErrorException
     */
    public function create(): string
    {
        $this->loadPageByType('order_approval_create');
        if (! customer(true)->can('order-approval.create')) {
            abort(403);
        }

        return $this->render();
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
        $this->loadPageByType('order_reject_detail');

        return $this->render();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (! customer(true)->can('order-approval.update')) {
            abort(403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
