<?php

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
                                                 * Run the database seeds.
                 *
                 * @return void
                 */
    public function run()
    {
        $items = [
            /**
         * Super Admin
             * - Full system access
             */
            [
                'id' => 1,
                'name' => 'super-admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access.',
            ],
            /**
             * Building Manager
             * - Full access to the assigned buildings.
             */
            [
                'id' => 2,
                'name' => 'building-manager',
                'display_name' => 'Building Manager',
                'description' => 'Full access to assigned buildings only.',
            ],
            /**
             * Admin/Reception Staff
             *  - Full access to bookings only.
             */
            [
                'id' => 3,
                'name' => 'admin',
                'display_name' => 'Admin/Reception',
                'description' => 'Full access to the bookings only.',
            ],
            /**
             * General Staff
             *  - Backend access to only the relevant data per assigned buildings 
             */
            [
                'id' => 4,
                'name' => 'general-staff',
                'display_name' => 'General Staff',
                'description' => 'Limited access to assigned buildings only.',
            ],
            /**
             * 3rd party access
             *  - access for 3rd party service providers
             */
            [
                'id' => 5,
                'name' => 'external',
                'display_name' => '3rd Party access',
                'description' => 'Limited access to own items ans bookings only.',
            ],
            /**
             * Residents
             *  - Front End access.
             *  - Full access to their own profile 
             *  - Can see and book Events, Rooms, Services, Hire Service in one or many assigned buildings.
             */
            [
                'id' => 6,
                'name' => 'resident',
                'display_name' => 'Resident',
                'description' => 'Full access to their own profile and for the front-end.',
            ],
            /**
             * Residents VIP
             *  - Front End access.
             *  - Full access to their own profile 
             *  - Can see and book Events, Rooms, Services, Hire Service in one or many assigned buildings.
             */
            [
                'id' => 7,
                'name' => 'resident-vip',
                'display_name' => 'Free of charge',
                'description' => 'Full access to their own profile and for the front-end. Resident level to book anything free of charge (no payment required)',
            ],
        ];

        foreach ($items as $item) {
            $role = Role::find($item['id']);
            if ($role) {
                // $role->update($item);
            } else {
                Role::create($item);
            }
        }



        // /**
        //  * Super Admin
        //  * - Full system access
        //  */
        // Role::create([
        //     'name' => 'super-admin',
        //     'display_name' => 'Super Admin',
        //     'description' => 'Full system access.',
        // ]);

        // /**
        //  * Building Manager
        //  * - Full access to the assigned buildings.
        //  */
        // Role::create([
        //     'name' => 'building-manager',
        //     'display_name' => 'Building Manager',
        //     'description' => 'Full access to assigned buildings only.',
        // ]);

        // /**
        //  * Admin/Reception Staff
        //  *  - Full access to bookings only.
        //  */
        // Role::create([
        //     'name' => 'admin',
        //     'display_name' => 'Admin/Reception',
        //     'description' => 'Full access to the bookings only.',
        // ]);

        // /**
        //  * General Staff
        //  *  - Backend access to only the relevant data per assigned buildings 
        //  */
        // Role::create([
        //     'name' => 'general-staff',
        //     'display_name' => 'General Staff',
        //     'description' => 'Limited access to assigned buildings only.',
        // ]);

        // /**
        //  * 3rd party access
        //  *  - access for 3rd party service providers
        //  */
        // Role::create([
        //     'name' => 'external',
        //     'display_name' => '3rd Party access',
        //     'description' => 'Limited access to own items ans bookings only.',
        // ]);


        // /**
        //  * Residents
        //  *  - Front End access.
        //  *  - Full access to their own profile 
        //  *  - Can see and book Events, Rooms, Services, Hire Service in one or many assigned buildings.
        //  */
        // Role::create([
        //     'name' => 'resident',
        //     'display_name' => 'Resident',
        //     'description' => 'Full access to their own profile and for the front-end.',
        // ]);

        // /**
        //  * Residents
        //  *  - Front End access.
        //  *  - Full access to their own profile 
        //  *  - Can see and book Events, Rooms, Services, Hire Service in one or many assigned buildings.
        //  */
        // // Role::create([
        // //     'name' => 'resident-vip',
        // //     'display_name' => 'Resident (VIP)',
        // //     'description' => 'Full access to their own profile and for the front-end.',
        // // ]);

    }
}
