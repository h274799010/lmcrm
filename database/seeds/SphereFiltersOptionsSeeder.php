<?php

use Illuminate\Database\Seeder;

class SphereFiltersOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('sphere_filters_options')->insert([
            'id' => 3,
            'sphere_ff_id' => '34',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'r1',
            'value' => 'r1',
            'position' => '',
            'created_at' => '2016-05-15 10:29:45',
            'updated_at' => '2016-06-07 05:18:24',
        ]);

        DB::table('sphere_filters_options')->insert([
            'id' => 4,
            'sphere_ff_id' => '34',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'r2',
            'value' => 'r2',
            'position' => '',
            'created_at' => '2016-05-15 10:29:46',
            'updated_at' => '2016-06-07 05:18:24',

        ]);

        DB::table('sphere_filters_options')->insert([
            'id' => 5,
            'sphere_ff_id' => '34',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'r3',
            'value' => 'r3',
            'position' => '',
            'created_at' => '2016-05-15 10:29:46',
            'updated_at' => '2016-06-07 05:18:24',

        ]);

        DB::table('sphere_filters_options')->insert([
            'id' => 6,
            'sphere_ff_id' => '35',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'c1',
            'value' => 'c1',
            'position' => '',
            'created_at' => '2016-06-07 05:18:24',
            'updated_at' => '2016-06-07 05:18:24',

        ]);

        DB::table('sphere_filters_options')->insert([
            'id' => 7,
            'sphere_ff_id' => '35',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'c2',
            'value' => 'c2',
            'position' => '',
            'created_at' => '2016-06-07 05:18:25',
            'updated_at' => '2016-06-07 05:18:25',

        ]);

        DB::table('sphere_filters_options')->insert([
            'id' => 8,
            'sphere_ff_id' => '35',
            'ctype' => 'agent',
            '_type' => 'option',
            'name' => 'c3',
            'value' => 'c3',
            'position' => '',
            'created_at' => '2016-06-07 05:18:25',
            'updated_at' => '2016-06-07 05:18:25',

        ]);
    }
}
