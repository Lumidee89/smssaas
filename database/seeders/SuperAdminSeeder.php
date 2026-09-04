<?php

// database/seeders/SuperAdminSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'admin@plus36networks.tech',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'school_id' => null,
        ]);
    }
}
