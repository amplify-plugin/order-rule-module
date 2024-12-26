<?php

use Amplify\System\OrderRule\Http\Controllers\CustomerOrderRuleController;
use Amplify\System\OrderRule\Http\Controllers\OrderApprovalController;
use Amplify\System\OrderRule\Http\Controllers\OrderAwaitingApprovalController;
use Amplify\System\OrderRule\Http\Controllers\OrderRejectController;
use Amplify\System\OrderRule\Http\Controllers\OrderRuleController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::group([
        'prefix' => config('backpack.base.route_prefix', 'backpack'),
        'middleware' => array_merge(
            config('backpack.base.web_middleware', ['web']),
            (array) config('backpack.base.middleware_key', 'admin'),
            ['admin_password_reset_required']
        ),
    ], function () {
        Route::crud('order-rule', 'Admin\OrderRuleCrudController');
        Route::crud('customer-order-rule', 'Admin\CustomerOrderRuleCrudController');
        Route::crud('customer-order-rule-track', 'Admin\CustomerOrderRuleTrackCrudController');
    });
});

Route::name('frontend.')->middleware(['web', 'customers'])->group(function () {
    Route::resource('order-rules', OrderRuleController::class);
    Route::resource('order-awaiting-approvals', OrderAwaitingApprovalController::class);
    Route::get('order/{order_id}/checkout', [OrderAwaitingApprovalController::class, 'confirm'])->name('order.checkout');
    Route::post('order/{order_id}/confirm', [OrderAwaitingApprovalController::class, 'action'])->name('order.action');
    Route::resource('order-approvals', OrderApprovalController::class);
    Route::resource('order-rejects', OrderRejectController::class);
    Route::post('cart/order-rules-check', [OrderRuleController::class, 'cartOrderRuleCheck']);
});

Route::post('/fetch/order-rule', [CustomerOrderRuleController::class, 'fetchAvailableRule'])->name('frontend.fetch.order-rule');
Route::post('/fetch/rule', [CustomerOrderRuleController::class, 'fetchRuleField'])->name('frontend.fetch.rule-field');
Route::get('/fetch/contacts/{customer_id}', [CustomerOrderRuleController::class, 'fetchContactsByCustomer'])->name('frontend.fetch.contacts');

Route::delete('/order-rule/{rule}', [CustomerOrderRuleController::class, 'destroy'])->name('order-rule.destroy');
Route::get('/order-rule', [DynamicPageLoadController::class, 'orderRuleList'])->name('frontend.order-rule');
Route::match(['post', 'put'], '/order-rule', [CustomerOrderRuleController::class, 'creataOrUpdate'])->name('frontend.save-order-rule');
