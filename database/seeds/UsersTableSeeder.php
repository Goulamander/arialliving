<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Start Creative
        User::create([
            'first_name' => 'StartCreative',
            'last_name'  => 'Support',
            'email'      => 'george@startcreative.com.au',
            'password'   =>  bcrypt('8t$N52Wt*w'),
            'activated'  => true,
            'status'     => true,
        ]);
    }

}
