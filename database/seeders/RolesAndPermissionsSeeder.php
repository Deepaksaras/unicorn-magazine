<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Article permissions
            ['name' => 'Create Articles', 'slug' => 'create-articles'],
            ['name' => 'Edit Articles', 'slug' => 'edit-articles'],
            ['name' => 'Delete Articles', 'slug' => 'delete-articles'],
            ['name' => 'Publish Articles', 'slug' => 'publish-articles'],

            // Category permissions
            ['name' => 'Manage Categories', 'slug' => 'manage-categories'],

            // Tag permissions
            ['name' => 'Manage Tags', 'slug' => 'manage-tags'],

            // Media permissions
            ['name' => 'Upload Media', 'slug' => 'upload-media'],
            ['name' => 'Delete Media', 'slug' => 'delete-media'],

            // User permissions
            ['name' => 'Manage Users', 'slug' => 'manage-users'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles'],

            // Comment permissions
            ['name' => 'Manage Comments', 'slug' => 'manage-comments'],

            // Settings permissions
            ['name' => 'Manage Settings', 'slug' => 'manage-settings'],

            // Report permissions
            ['name' => 'Manage Reports', 'slug' => 'manage-reports'],

            // Ad permissions
            ['name' => 'Manage Advertisements', 'slug' => 'manage-advertisements'],

            // Subscriber permissions
            ['name' => 'Manage Subscribers', 'slug' => 'manage-subscribers'],

            // Page permissions
            ['name' => 'Manage Pages', 'slug' => 'manage-pages'],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => $permission['name'] . ' permission',
                'status' => 1,
            ]);
        }

        // Create roles
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to all features',
            'status' => 1,
        ]);

        $editorRole = Role::create([
            'name' => 'Editor',
            'slug' => 'editor',
            'description' => 'Can manage content but not settings',
            'status' => 1,
        ]);

        $authorRole = Role::create([
            'name' => 'Author',
            'slug' => 'author',
            'description' => 'Can create and edit own content',
            'status' => 1,
        ]);

        // Assign all permissions to admin
        $allPermissions = Permission::pluck('id')->toArray();
        $adminRole->permissions()->attach($allPermissions);

        // Assign content permissions to editor
        $editorPermissions = Permission::whereIn('slug', [
            'create-articles', 'edit-articles', 'delete-articles', 'publish-articles',
            'manage-categories', 'manage-tags', 'upload-media', 'delete-media',
            'manage-comments', 'manage-reports', 'manage-pages',
        ])->pluck('id')->toArray();
        $editorRole->permissions()->attach($editorPermissions);

        // Assign limited permissions to author
        $authorPermissions = Permission::whereIn('slug', [
            'create-articles', 'edit-articles', 'upload-media',
        ])->pluck('id')->toArray();
        $authorRole->permissions()->attach($authorPermissions);
    }
}
