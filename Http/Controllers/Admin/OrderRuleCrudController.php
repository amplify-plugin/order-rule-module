<?php

namespace Amplify\System\OrderRule\Http\Controllers\Admin;

use Amplify\System\OrderRule\Http\Requests\OrderRuleRequest;
use Amplify\System\OrderRule\Models\OrderRule;
use Amplify\System\Abstracts\BackpackCustomCrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class OrderRuleCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrderRuleCrudController extends BackpackCustomCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    private $rules_options;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(OrderRule::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/order-rule');
        CRUD::setEntityNameStrings('order-rule', 'order rules');
        /**
         * Add the rule label with rule key for front-end rendering.
         *
         * @see /config/amplify/constant.php
         */
        $this->rules_options = config('amplify.constant.order-rules-label');
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
        CRUD::column('name');
        CRUD::column('short_code')
            ->type('select_from_array')
            ->label('Short code for select rule')
            ->options($this->rules_options);
        CRUD::column('description');
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
        CRUD::setValidation(OrderRuleRequest::class);

        CRUD::addField([
            'name' => 'name',
            'attributes' => [
                'placeholder' => 'Write rule name.',
            ],
        ]);

        CRUD::addField([
            'name' => 'short_code',
            'label' => 'Short code for select rule',
            'type' => 'select2_from_array',
            'options' => $this->rules_options,
            'attributes' => ['id' => 'order_rule_short_code'],
            'allows_null' => true,
        ]);

        CRUD::addField([
            'name' => 'description',
            'attributes' => [
                'placeholder' => 'Write rule description',
            ],
        ]);
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

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     *
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->autoSetupShowOperation();

        CRUD::column('short_code')->type('select_from_array')->options($this->rules_options);
    }
}
