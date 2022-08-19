<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Roles
        $SuperAdminRole = Role::where('name', 'super-admin')->first();

        // Users
        $user = User::UserByEmail('george@startcreative.com.au');

        $user->attachRole($SuperAdminRole);
    }
}
