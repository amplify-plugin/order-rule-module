<?php

namespace Amplify\System\OrderRule\Http\Controllers\Admin;

use Amplify\System\OrderRule\Http\Requests\CustomerOrderRuleTrackRequest;
use Amplify\System\OrderRule\Models\CustomerOrderRuleTrack;
use Amplify\System\Abstracts\BackpackCustomCrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CustomerOrderRuleTrackCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CustomerOrderRuleTrackCrudController extends BackpackCustomCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(CustomerOrderRuleTrack::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/customer-order-rule-track');
        CRUD::setEntityNameStrings('customer-order-rule-track', 'customer order rule tracks');
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
        CRUD::column('customer_order_id');
        CRUD::column('order_rule_id');
        CRUD::column('status');
        CRUD::column('notes');
        CRUD::column('created_at');
        CRUD::column('updated_at');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
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
        CRUD::setValidation(CustomerOrderRuleTrackRequest::class);

        CRUD::addFields([
            [
                'name' => 'customer_order_id',
                'type' => 'select2',
                'entity' => 'customerOrder',
                'attribute' => 'id',
            ], [
                'name' => 'order_rule_id',
                'type' => 'select2',
                'entity' => 'orderRule',
                'attribute' => 'name',
                'options' => (fn ($query) => $query->orderBy('name')->get()),
            ], [
                'name' => 'approver_id',
                'type' => 'select2',
                'entity' => 'approver',
                'attribute' => 'name',
                'options' => (fn ($query) => $query->orderBy('name')->get()),
            ], [
                'name' => 'status',
                'type' => 'select2_from_array',
                'options' => CustomerOrderRuleTrack::STATUS,
                'default' => 'pending',
            ],
        ]);
        CRUD::field('notes');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
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
    }
}
