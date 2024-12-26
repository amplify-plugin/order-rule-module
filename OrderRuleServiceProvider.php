<?php

namespace Amplify\System\OrderRule;

use Illuminate\Support\ServiceProvider;

class OrderRuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('OrderRuleCheck', function () {
            return new OrderRuleCheck;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
    }
}
