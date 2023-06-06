<?php

declare(strict_types=1);

namespace Database\Seeders\Rsi\CommLink;

use App\Models\Rsi\CommLink\Category\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Category::query()->count() > 0) {
            return;
        }

        DB::table('comm_link_categories')->insert(
            [
                'name' => 'Undefined',
                'slug' => 'undefined',
            ]
        );
    }
}
