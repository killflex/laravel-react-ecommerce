<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Electronics',
                'slug' => Str::slug('electronics'),
                'meta_title' => 'Electronics Department',
                'meta_description' => 'All kinds of electronic items',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'slug' => Str::slug('fashion'),
                'meta_title' => 'Fashion Department',
                'meta_description' => 'Latest fashion trends',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Health & Wellness',
                'slug' => Str::slug('health-wellness'),
                'meta_title' => 'Health and Wellness Department',
                'meta_description' => 'Products for a healthy lifestyle',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('departments')->insert($departments);
    }
}
