<?php

namespace Amplify\System\OrderRule\Http\Controllers\Admin;

use Amplify\System\OrderRule\Http\Requests\CustomerOrderRuleRequest;
use Amplify\System\OrderRule\Models\CustomerOrderRule;
use Amplify\System\OrderRule\Models\OrderRule;
use App\Abstracts\BackpackCustomCrudController;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Backpack\Pro\Http\Controllers\Operations\FetchOperation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Class CustomerOrderRuleCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomerOrderRuleCrudController extends BackpackCustomCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as storeTrait;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as updateTrait;
    }
    use FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(CustomerOrderRule::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/customer-order-rule');
        CRUD::setEntityNameStrings('customer-order-rule', 'customer order rules');
        CRUD::addButtonFromView('top', 'import-customer-order-rule', 'import-customer-order-rule', 'end');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::removeButton('show');
        CRUD::column('customer.customer_name')->label('Customer');
        CRUD::column('name');
        CRUD::column('orderRule')->label('Rule');
        CRUD::column('enabled')->type('boolean');
        CRUD::column('updated_at');

        Widget::add()->type('script')->content('assets/js/admin/forms/customer-order-rule.js');
    }

    protected function setupCustomRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/import', [
            'as' => $routeName.'.import',
            'uses' => $controller.'@import',
            'operation' => 'import',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {

        CRUD::setValidation(CustomerOrderRuleRequest::class);

        $this->data['customer_list'] = Customer::orderBy('customer_name')
            ->select(['id', 'customer_name'])->get()
            ->toArray();

        CRUD::setCreateView('crud::pages.customer-order-rules.create');

        $this->crud->setCreateContentClass('col-md-12');

        CRUD::addField([
            'label' => 'Name',
            'name' => 'name',
            'attributes' => [
                'placeholder' => 'Write name',
            ],
        ]);

        CRUD::addField([
            'label' => 'Customer',
            'name' => 'customer_id',
            'allows_null' => false,
            'placeholder' => 'Select customer',
            'attribute' => 'customer_name',
            'attributes' => [
                'id' => 'customer_id',
            ],

        ]);

        CRUD::addField([
            'name' => 'order_rule_id',
            'label' => 'Order Rule',
            'entity' => 'orderRule',
            'allows_null' => false,
            'placeholder' => 'Select a rule',
            'attributes' => [
                'id' => 'order_rule',
            ],
        ]);

        CRUD::field('enabled');

        CRUD::field('description');

        CRUD::addField([
            'type' => 'hidden',
            'name' => 'value',
            'value' => 'create',
        ]);

    }

    public function setupShowOperation()
    {
        CRUD::addColumn([
            'label' => 'Rule Values',
            'name' => 'value',
        ]);
        CRUD::addColumn([
            'label' => 'Order Rule',
            'name' => 'orderRule',
            'type' => 'relationship',
        ]);

        $this->autoSetupShowOperation();

    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->crud->setEditContentClass('col-md-12');

        CRUD::setEditView('crud::pages.customer-order-rules.create');
    }

    public function store()
    {
        $this->storeTrait();

        return response()->json([
            'message' => 'Successfully created.',
            'redirect_url' => 'admin/customer-order-rule',
        ], 200);
    }

    public function update()
    {
        $this->updateTrait();

        return response()->json([
            'message' => 'Successfully updated.',
            'redirect_url' => 'admin/customer-order-rule',
        ], 200);
    }

    public function fetchOrderRule()
    {
        $customerAlreadyUsedRules = CustomerOrderRule::where('customer_id', '=', \request('customer_id'))->orderBy('name')->get()
            ->pluck('order_rule_id')->toArray();

        return $this->fetch([
            'model' => OrderRule::class,
            'paginate' => false,
            'query' => function ($model) use ($customerAlreadyUsedRules) {
                if (\request('method') == 'post') {
                    return $model->whereNotIn('id', $customerAlreadyUsedRules);
                } else {
                    return $model->whereIn('id', $customerAlreadyUsedRules);
                }
            },
        ]);
    }

    /**
     * Fetch the rule values format
     *
     * @return JsonResponse
     */
    public function fetchRule()
    {
        $customerOrderRule = CustomerOrderRule::where(['order_rule_id' => \request('order_rule_id'), 'customer_id' => \request('customer_id')])->first();

        $orderRule = OrderRule::find(\request('order_rule_id'));

        $metaData = $orderRule->target_rule->meta(\request()->all());

        return response()->json([
            'data' => $customerOrderRule ? $customerOrderRule->value : null,
            'meta' => $metaData,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv',
        ]);

        $file = $request->file('csv_file');
        $fileContents = file($file->getPathname());
        $orderRule = OrderRule::whereShortCode('sub-total-rule')->firstOrFail();

        foreach ($fileContents as $key => $line) {
            if ($key === 0) {
                continue;
            }
            $data = str_getcsv($line);
            $customer = Customer::whereCustomerCode($data[3])->firstOrFail();
            $customerOrderRule = CustomerOrderRule::where('order_rule_id', $orderRule->id)->where('customer_id', $customer->id)->first();

            set_customer_team_id($customer->id);
            $priceRange = $this->parseAmount($data[4]);
            $contacts = explode(',', $data[2]);
            $approvers = explode(',', $data[6]);
            $shipTo = explode(',', $data[5]);
            $ship_to_id_list = ! empty($shipTo) ? CustomerAddress::where('customer_id', $customer->id)->whereIn('address_code', $shipTo)->pluck('address_code')->toArray() : [];
            $contacts_id_list = ! empty($contacts) ? Contact::where('customer_id', $customer->id)->whereIn('email', $contacts)->pluck('id')->toArray() : [];
            $approvers_id_list = ! empty($approvers) ? Contact::where('customer_id', $customer->id)
                ->wherehas('ownPermissions', fn ($query) => $query->where('name', 'order-approval.approve'))
                ->whereIn('email', $approvers)
                ->pluck('id')->toArray() : [];

            if ($customerOrderRule) {
                $existingRules = $customerOrderRule->value;
                $filteredItem = array_filter($existingRules, fn ($item) => $item['from_amount'] == $priceRange['from_amount'] && $item['to_amount'] == $priceRange['to_amount']);

                if (empty($filteredItem)) {
                    array_push($existingRules, [
                        'ship_to' => $ship_to_id_list,
                        'contacts' => $contacts_id_list,
                        'approvers' => $approvers_id_list,
                        'to_amount' => $priceRange['to_amount'],
                        'from_amount' => $priceRange['from_amount'],
                    ]);
                } else {
                    foreach ($filteredItem as $key => $item) {
                        $existingRules[$key] = [
                            'ship_to' => array_unique(array_merge($item['ship_to'], $ship_to_id_list)),
                            'contacts' => array_unique(array_merge($item['contacts'], $contacts_id_list)),
                            'approvers' => array_unique(array_merge($item['approvers'], $approvers_id_list)),
                            'to_amount' => $item['to_amount'],
                            'from_amount' => $item['from_amount'],
                        ];
                    }
                }

                $customerOrderRule->update(['value' => $existingRules]);
            } else {
                CustomerOrderRule::create([
                    'name' => $data[0] ?: $orderRule->name,
                    'description' => $data[1],
                    'order_rule_id' => $orderRule->id,
                    'customer_id' => $customer->id,
                    'enabled' => true,
                    'value' => [
                        [
                            'ship_to' => $ship_to_id_list,
                            'contacts' => $contacts_id_list,
                            'approvers' => $approvers_id_list,
                            'to_amount' => $priceRange['to_amount'],
                            'from_amount' => $priceRange['from_amount'],
                        ],
                    ],
                ]);
            }
        }

        return back();
    }

    private function parseAmount($value)
    {
        preg_match_all('/\d+/', $value, $matches);
        $numbers = array_map('intval', $matches[0]);

        return [
            'from_amount' => $numbers[0] ?? 0,
            'to_amount' => count($numbers) === 2 ? $numbers[1] : null,
        ];
    }
}
