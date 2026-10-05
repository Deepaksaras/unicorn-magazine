<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@unicornmagazine.com',
            'password' => Hash::make('password'),
            'phone' => '+91 9876543210',
            'bio' => 'Super administrator of The Unicorn Magazine',
            'designation' => 'Administrator',
            'status' => 1,
        ]);
        $admin->assignRole('super-admin');

        // Create editor user
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor@unicornmagazine.com',
            'password' => Hash::make('password'),
            'phone' => '+91 9876543211',
            'bio' => 'Chief editor of The Unicorn Magazine',
            'designation' => 'Editor-in-Chief',
            'status' => 1,
        ]);
        $editor->assignRole('editor');

        // Create author user
        $author = User::create([
            'name' => 'Author User',
            'email' => 'author@unicornmagazine.com',
            'password' => Hash::make('password'),
            'phone' => '+91 9876543212',
            'bio' => 'Staff writer at The Unicorn Magazine',
            'designation' => 'Senior Writer',
            'status' => 1,
        ]);
        $author->assignRole('author');
    }
}
