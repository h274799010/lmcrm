
<?php



use Illuminate\Database\Seeder;



class SentinelDatabaseSeeder extends Seeder

{

    /**

     * Run the database seeds.

     *

     * @return void

     */

    public function run()

    {

        // Create Users

        DB::table('users')->truncate();



        $system = Sentinel::getUserRepository()->create(array(

            'email'    => 'system@system.com',
            'password' => 'system',
            'first_name' => 'system',
            'last_name' => 'system'

        ));



        $admin = Sentinel::getUserRepository()->create(array(

            'email'    => 'admin@admin.com',
            'password' => 'admin',
            'first_name' => 'admin',
            'last_name' => 'admin'

        ));



        /*$agent = Sentinel::getUserRepository()->create(array(

            'email'    => 'agent@agent.com',
            'password' => 'agent',
            'name' => 'agent'

        ));

        $operator = Sentinel::getUserRepository()->create(array(

            'email'    => 'operator@operator.com',
            'password' => 'operator',
            'name' => 'operator'

        ));

        // agents
        $dealmaker = Sentinel::getUserRepository()->create(array(

            'email'    => 'dealmaker@dealmaker.com',
            'password' => 'dealmaker',
            'name' => 'dealmaker'

        ));
        $leadbayer = Sentinel::getUserRepository()->create(array(

            'email'    => 'leadbayer@leadbayer.com',
            'password' => 'leadbayer',
            'name' => 'leadbayer'

        ));
        $partner = Sentinel::getUserRepository()->create(array(

            'email'    => 'partner@partner.com',
            'password' => 'partner',
            'name' => 'partner'

        ));

        $accountManager = Sentinel::getUserRepository()->create(array(

            'email'    => 'account@account.com',
            'password' => 'account',
            'name' => 'account manager'

        ));*/


        // Create Activations

        DB::table('activations')->truncate();

        $code = Activation::create($admin)->code;

        Activation::complete($admin, $code);

        $code = Activation::create($system)->code;

        Activation::complete($system, $code);

        /*$code = Activation::create($agent)->code;

        Activation::complete($agent, $code);

        $code = Activation::create($operator)->code;

        Activation::complete($operator, $code);

        // agents activate
        $code = Activation::create($dealmaker)->code;

        Activation::complete($dealmaker, $code);

        $code = Activation::create($leadbayer)->code;

        Activation::complete($leadbayer, $code);

        $code = Activation::create($partner)->code;

        Activation::complete($partner, $code);

        $code = Activation::create($accountManager)->code;

        Activation::complete($accountManager, $code);*/


        // Create Roles

        $administratorRole = Sentinel::getRoleRepository()->create(array(

            'name' => 'Administrator',

            'slug' => 'administrator',

            'permissions' => array(

                'users.create' => true,

                'users.update' => true,

                'users.view' => true,

                'users.destroy' => true,

                'roles.create' => true,

                'roles.update' => true,

                'roles.view' => true,

                'roles.delete' => true

            )

        ));

        $agentRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Agent',
            'slug' => 'agent',
            'permissions' => [
                "agent.sphere.index" => true,
                "agent.sphere.edit" => true,
                "agent.sphere.update" => true,
                "agent.sphere.removeMask" => true,
                "agent.salesman.index" => true,
                "agent.salesman.create" => true,
                "agent.salesman.store" => true,
                "agent.salesman.edit" => true,
                "agent.salesman.update" => true
            ]
        ));

        $salesmanRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Salesman',
            'slug' => 'salesman',
            'permissions' => [
                "agent.sphere.index" => false,
                "agent.sphere.edit" => false,
                "agent.sphere.update" => false,
                "agent.sphere.removeMask" => false,
                "agent.salesman.index" => false,
                "agent.salesman.create" => false,
                "agent.salesman.store" => false,
                "agent.salesman.edit" => false,
                "agent.salesman.update" => false
            ]
        ));

        $operatorRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Operator',
            'slug' => 'operator',
            'permissions' => array()
        ));

        // agents roles
        $dealmakerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Deal maker',
            'slug' => 'dealmaker',
            'permissions' => [
                "agent.lead.deposited"=> true,
                "agent.lead.create"=> true,
                "agent.lead.store"=> true,
                "agent.lead.obtain"=> true,
                "agent.lead.opened"=> true,
                "agent.lead.open"=> true,
                "agent.lead.openAll"=> false,
                "salesman.lead.obtain"=> true,
                "salesman.lead.deposited"=> true,
                "salesman.lead.opened"=> true,
                "agent.salesman.obtainedLead" => true,
                "agent.salesman.obtain.data" => true,
                "agent.salesman.openedLeads" => true,
                "agent.salesman.openedLeadAjax" => true,
                "agent.salesman.sphere.index" => true,
                "agent.salesman.sphere.edit" => true,
                "agent.salesman.sphere.update" => true
            ]
        ));

        $leadbayerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Lead bayer',
            'slug' => 'leadbayer',
            'permissions' => [
                "agent.lead.deposited"=> true,
                "agent.lead.create"=> true,
                "agent.lead.store"=> true,
                "agent.lead.obtain"=> true,
                "agent.lead.opened"=> true,
                "agent.lead.open"=> true,
                "agent.lead.openAll"=> true,
                "salesman.lead.obtain"=> true,
                "salesman.lead.deposited"=> true,
                "salesman.lead.opened"=> true,
                "agent.salesman.obtainedLead" => true,
                "agent.salesman.obtain.data" => true,
                "agent.salesman.openedLeads" => true,
                "agent.salesman.openedLeadAjax" => true,
                "agent.salesman.sphere.index" => true,
                "agent.salesman.sphere.edit" => true,
                "agent.salesman.sphere.update" => true
            ]
        ));

        $partnerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Partner',
            'slug' => 'partner',
            'permissions' => [
                "agent.lead.deposited"=> true,
                "agent.lead.create"=> true,
                "agent.lead.store"=> true,
                "agent.lead.obtain"=> false,
                "agent.lead.opened"=> false,
                "agent.lead.open"=> false,
                "agent.lead.openAll"=> false,
                "salesman.lead.obtain"=> false,
                "salesman.lead.deposited"=> true,
                "salesman.lead.opened"=> false,
                "agent.salesman.obtainedLead" => false,
                "agent.salesman.obtain.data" => false,
                "agent.salesman.openedLeads" => false,
                "agent.salesman.openedLeadAjax" => false,
                "agent.salesman.sphere.index" => false,
                "agent.salesman.sphere.edit" => false,
                "agent.salesman.sphere.update" => false
            ]
        ));

        $accountManagerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Account Manager',
            'slug' => 'account_manager',
            'permissions' => []
        ));

        // Assign Roles to Users

        $administratorRole->users()->attach($admin);

        /*$agentRole->users()->attach($agent);
        $partnerRole->users()->attach($agent);


        $operatorRole->users()->attach($operator);

        // set agents roles
        $dealmakerRole->users()->attach($dealmaker);
        $agentRole->users()->attach($dealmaker);

        $leadbayerRole->users()->attach($leadbayer);
        $agentRole->users()->attach($leadbayer);

        $partnerRole->users()->attach($partner);
        $agentRole->users()->attach($partner);

        $accountManagerRole->users()->attach($accountManager);*/

    }
}
