<?php

namespace Database\Seeders;

use Route;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'name' => 'Super-Admin',
            'guard_name' => 'api',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\Models\User',
            'model_id' => 1
        ]);


        $collection = Route::getRoutes();

        foreach ($collection as $route) {
            $routeName = $route->getName();
            if ($routeName) {
                $routeParts = explode('.', $routeName);

                if ($routeParts[0] !== 'passport') {
                    // create permissions
                    Permission::create(['guard_name' => 'api', 'name' => $routeParts[0] .' '. $routeParts[1]]);
                }

                $role1 = Role::find(1);
                $permissions = Permission::get()->pluck('name');
                foreach ( $permissions as $permission) {
                    $role1->givePermissionTo($permission);
                }

            }

        }








        // \Artisan::call('permissions:update');

        // $permissions = Permission::pluck('id');
        // foreach ($permissions as $permission) {
        //     DB::table('role_has_permissions')->insert([
        //         'permission_id' => $permission,
        //         'role_id' => 1,
        //     ]);
        // }
    }
}
