<<<<<<< HEAD
<?phpuse Illuminate\Database\Seeder;class SentinelDatabaseSeeder extends Seeder{    /**     * Run the database seeds.     *     * @return void     */    public function run()    {        // Create Users        DB::table('users')->truncate();        $admin = Sentinel::getUserRepository()->create(array(            'email'    => 'admin@admin.com',            'password' => 'admin'        ));        $agent = Sentinel::getUserRepository()->create(array(            'email'    => 'agent@agent.com',            'password' => 'agent'        ));        $operator = Sentinel::getUserRepository()->create(array(            'email'    => 'operator@operator.com',            'password' => 'operator'        ));        // agents        $dealmaker = Sentinel::getUserRepository()->create(array(            'email'    => 'dealmaker@dealmaker.com',            'password' => 'dealmaker'        ));        $leadbayer = Sentinel::getUserRepository()->create(array(            'email'    => 'leadbayer@leadbayer.com',            'password' => 'leadbayer'        ));        $partner = Sentinel::getUserRepository()->create(array(            'email'    => 'partner@partner.com',            'password' => 'partner'        ));        // Create Activations        DB::table('activations')->truncate();        $code = Activation::create($admin)->code;        Activation::complete($admin, $code);        $code = Activation::create($agent)->code;        Activation::complete($agent, $code);        $code = Activation::create($operator)->code;        Activation::complete($operator, $code);        // agents activate        $code = Activation::create($dealmaker)->code;        Activation::complete($dealmaker, $code);        $code = Activation::create($leadbayer)->code;        Activation::complete($leadbayer, $code);        $code = Activation::create($partner)->code;        Activation::complete($partner, $code);        // Create Roles        $administratorRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Administrator',            'slug' => 'administrator',            'permissions' => array(                'users.create' => true,                'users.update' => true,                'users.view' => true,                'users.destroy' => true,                'roles.create' => true,                'roles.update' => true,                'roles.view' => true,                'roles.delete' => true            )        ));        $agentRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Agent',            'slug' => 'agent',            'permissions' => array()        ));        $salesmanRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Salesman',            'slug' => 'salesman',            'permissions' => array()        ));        $operatorRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Operator',            'slug' => 'operator',            'permissions' => array()        ));        // agents roles        $dealmakerRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Deal maker',            'slug' => 'dealmaker',            'permissions' => array(                "agent.lead.deposited" => true,                "agent.lead.create" => true,                "agent.lead.store" => true,                "agent.lead.obtain" => true,                "agent.lead.opened" => true,                "agent.lead.open" => true,                "agent.lead.openAll" => false            )        ));        $leadbayerRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Lead bayer',            'slug' => 'leadbayer',            'permissions' => array(                "agent.lead.deposited" => true,                "agent.lead.create" => true,                "agent.lead.store" => true,                "agent.lead.obtain" => true,                "agent.lead.opened" => true,                "agent.lead.open" => true,                "agent.lead.openAll" => true            )        ));        $partnerRole = Sentinel::getRoleRepository()->create(array(            'name' => 'Partner',            'slug' => 'partner',            'permissions' => array(                "agent.lead.deposited" => true,                "agent.lead.create" => true,                "agent.lead.store" => true,                "agent.lead.obtain" => false,                "agent.lead.opened" => false,                "agent.lead.open" => false,                "agent.lead.openAll" => false            )        ));        // Assign Roles to Users        $administratorRole->users()->attach($admin);        $agentRole->users()->attach($agent);        $operatorRole->users()->attach($operator);        // set agents roles        $dealmakerRole->users()->attach($dealmaker);        $dealmakerRole->users()->attach($dealmaker);        $leadbayerRole->users()->attach($leadbayer);        $leadbayerRole->users()->attach($leadbayer);        $partnerRole->users()->attach($partner);        $partnerRole->users()->attach($partner);    }}
=======
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



        Sentinel::getUserRepository()->create(array(

            'email'    => 'system@system.com',
            'password' => 'system',
            'name' => 'system'

        ));



        $admin = Sentinel::getUserRepository()->create(array(

            'email'    => 'admin@admin.com',
            'password' => 'admin',
            'name' => 'admin'

        ));



        $agent = Sentinel::getUserRepository()->create(array(

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
            'password' => 'dealmaker'

        ));
        $leadbayer = Sentinel::getUserRepository()->create(array(

            'email'    => 'leadbayer@leadbayer.com',
            'password' => 'leadbayer'

        ));
        $partner = Sentinel::getUserRepository()->create(array(

            'email'    => 'partner@partner.com',
            'password' => 'partner'

        ));


        // Create Activations

        DB::table('activations')->truncate();

        $code = Activation::create($admin)->code;

        Activation::complete($admin, $code);

        $code = Activation::create($agent)->code;

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
            'permissions' => array()
        ));

        $salesmanRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Salesman',
            'slug' => 'salesman',
            'permissions' => array()
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
            'permissions' => array(
                "agent.lead.deposited" => true,
                "agent.lead.create" => true,
                "agent.lead.store" => true,
                "agent.lead.obtain" => true,
                "agent.lead.opened" => true,
                "agent.lead.open" => true,
                "agent.lead.openAll" => false
            )
        ));
        $leadbayerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Lead bayer',
            'slug' => 'leadbayer',
            'permissions' => array(
                "agent.lead.deposited" => true,
                "agent.lead.create" => true,
                "agent.lead.store" => true,
                "agent.lead.obtain" => true,
                "agent.lead.opened" => true,
                "agent.lead.open" => true,
                "agent.lead.openAll" => true
            )
        ));
        $partnerRole = Sentinel::getRoleRepository()->create(array(
            'name' => 'Partner',
            'slug' => 'partner',
            'permissions' => array(
                "agent.lead.deposited" => true,
                "agent.lead.create" => true,
                "agent.lead.store" => true,
                "agent.lead.obtain" => false,
                "agent.lead.opened" => false,
                "agent.lead.open" => false,
                "agent.lead.openAll" => false
            )
        ));

        // Assign Roles to Users

        $administratorRole->users()->attach($admin);

        $agentRole->users()->attach($agent);
        $partnerRole->users()->attach($agent);


        $operatorRole->users()->attach($operator);

        // set agents roles
        $dealmakerRole->users()->attach($dealmaker);
        $agentRole->users()->attach($dealmaker);

        $leadbayerRole->users()->attach($leadbayer);
        $agentRole->users()->attach($leadbayer);

        $partnerRole->users()->attach($partner);
        $agentRole->users()->attach($partner);

    }

}
>>>>>>> 6e773a8d0ea5d7bc1e1c62132b8ce265440a59b6
