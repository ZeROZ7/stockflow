<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Manager',
            'email' => 'demo@stockflow.test',
            'role' => Role::Manager,
        ]);

        User::factory()->create([
            'name' => 'Demo Staff',
            'email' => 'staff@stockflow.test',
            'role' => Role::Staff,
        ]);

        $this->call([
            CatalogSeeder::class,
            PurchaseOrderSeeder::class,
        ]);
    }
}
