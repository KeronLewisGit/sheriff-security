<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Production-safe: creates the vendor-pack document slots (only into an empty
     * table) and the first administrator when ADMIN_EMAIL and ADMIN_PASSWORD are set.
     */
    public function run(): void
    {
        $this->call(ComplianceDocumentSeeder::class);

        $email = config('sheriff.admin.email');
        $password = config('sheriff.admin.password');

        if ($email && $password) {
            User::firstOrCreate(['email' => strtolower((string) $email)], [
                'name' => config('sheriff.admin.name'),
                'password' => Hash::make((string) $password),
                'role' => 'admin',
            ]);
        }
    }
}
