<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetCategorySeeder extends Seeder
{
    public function run()
    {
        $now = now();
        $categories = [
            ['name' => 'Clinical supports', 'description' => 'Clinical and health related supports', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Independence', 'description' => 'Supports for independence', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Everyday living', 'description' => 'Daily living services', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Care management', 'description' => 'Care coordination and management', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AT-HM', 'description' => 'Assistive technology and home modifications', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Other', 'description' => 'Other budget items', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('budget_categories')->insertOrIgnore($categories);
    }
}
