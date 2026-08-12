<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. Insert ONLY Admin Role (others created from admin panel)
        // ==========================================
        DB::table('roles')->insert([
            'role_id' => 1,
            'role_name' => 'Admin',
            'description' => 'Super Administrator - Full system access',
            'status' => 1,
        ]);

        // ==========================================
        // 2. Insert Page Categories (Admin Panel Navigation)
        // ==========================================
        DB::table('page_categories')->insert([
            [
                'category_id' => 1,
                'category_name' => 'Dashboard',
                'description' => 'Dashboard pages',
                'status' => 1,
            ],
            [
                'category_id' => 2,
                'category_name' => 'User Management',
                'description' => 'Manage users, roles and permissions',
                'status' => 1,
            ],
        ]);

        // ==========================================
        // 3. Insert Pages (Admin Panel Pages)
        // ==========================================
        DB::table('pages')->insert([
            [
                'page_id' => 1,
                'page_name' => 'Admin Dashboard',
                'page_code' => 'ADMIN_DASHBOARD',
                'route_name' => 'admin.dashboard',
                'description' => 'Main admin dashboard',
                'category_id' => 1,
                'status' => 1,
            ],
            [
                'page_id' => 2,
                'page_name' => 'Users',
                'page_code' => 'USER_LIST',
                'route_name' => 'admin.users.index',
                'description' => 'Manage system users',
                'category_id' => 2,
                'status' => 1,
            ],
            [
                'page_id' => 3,
                'page_name' => 'Roles',
                'page_code' => 'ROLE_LIST',
                'route_name' => 'admin.roles.index',
                'description' => 'Manage user roles',
                'category_id' => 2,
                'status' => 1,
            ],
            [
                'page_id' => 4,
                'page_name' => 'Permissions',
                'page_code' => 'PERMISSION_MANAGE',
                'route_name' => 'admin.permissions.index',
                'description' => 'Assign permissions to roles',
                'category_id' => 2,
                'status' => 1,
            ],
        ]);

        // ==========================================
        // 4. Insert Role Options (Action buttons)
        // ==========================================
        DB::table('role_options')->insert([
            // Users page
            ['option_name' => 'Add User', 'option_code' => 'USER_ADD', 'page_id' => 2, 'status' => 1],
            ['option_name' => 'Edit User', 'option_code' => 'USER_EDIT', 'page_id' => 2, 'status' => 1],
            ['option_name' => 'Delete User', 'option_code' => 'USER_DELETE', 'page_id' => 2, 'status' => 1],
            ['option_name' => 'View User', 'option_code' => 'USER_VIEW', 'page_id' => 2, 'status' => 1],

            // Roles page
            ['option_name' => 'Add Role', 'option_code' => 'ROLE_ADD', 'page_id' => 3, 'status' => 1],
            ['option_name' => 'Edit Role', 'option_code' => 'ROLE_EDIT', 'page_id' => 3, 'status' => 1],
            ['option_name' => 'Delete Role', 'option_code' => 'ROLE_DELETE', 'page_id' => 3, 'status' => 1],

            // Permissions page
            ['option_name' => 'Assign Permission', 'option_code' => 'PERMISSION_ASSIGN', 'page_id' => 4, 'status' => 1],
        ]);

        // ==========================================
        // 5. Give Admin ALL page permissions
        // ==========================================
        $pages = DB::table('pages')->get();
        foreach ($pages as $page) {
            DB::table('role_permissions')->insert([
                'role_id' => 1,
                'page_code' => $page->page_code,
                'allow' => 1,
            ]);
        }

        // ==========================================
        // 6. Give Admin ALL option permissions
        // ==========================================
        $options = DB::table('role_options')->get();
        foreach ($options as $option) {
            DB::table('role_option_permissions')->insert([
                'roles_id' => 1,
                'option_code' => $option->option_code,
                'allow' => 1,
            ]);
        }

        // ==========================================
        // 7. Create Default Admin User
        // ==========================================
        DB::table('users')->insert([
            'user_name' => 'admin',
            'email' => 'admin@restaurant.com',
            'password' => Hash::make('admin123'),
            'must_change_password' => 0,
            'role_id' => 1,
            'status' => 1,
        ]);

        echo "\n✅ Admin account created successfully!\n";
        echo "📧 Email: admin@restaurant.com\n";
        echo "🔑 Password: admin123\n\n";
    }
}