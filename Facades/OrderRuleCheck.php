<?php

namespace Amplify\System\OrderRule\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class OrderRuleCheck
 *
 * @method static string check($order)
 * @method static string cartCheck($shipToName)
 */
class OrderRuleCheck extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'OrderRuleCheck';
    }
}
