<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Politics',
                'slug' => 'politics',
                'description' => 'Political events, elections, and government decisions',
                'icon' => 'government',
                'color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Football, basketball, tennis, and other sporting events',
                'icon' => 'trophy',
                'color' => '#10B981',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Entertainment',
                'slug' => 'entertainment',
                'description' => 'Movies, music, celebrities, and entertainment industry',
                'icon' => 'film',
                'color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Technology',
                'slug' => 'technology',
                'description' => 'Tech companies, product launches, and innovation',
                'icon' => 'cpu',
                'color' => '#8B5CF6',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Economics',
                'slug' => 'economics',
                'description' => 'Market trends, currency, and economic indicators',
                'icon' => 'trending-up',
                'color' => '#EF4444',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Weather',
                'slug' => 'weather',
                'description' => 'Weather predictions and climate events',
                'icon' => 'cloud',
                'color' => '#06B6D4',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Social Media',
                'slug' => 'social-media',
                'description' => 'Social media trends and viral content predictions',
                'icon' => 'share',
                'color' => '#EC4899',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Cryptocurrency',
                'slug' => 'cryptocurrency',
                'description' => 'Bitcoin, Ethereum, and other crypto predictions',
                'icon' => 'dollar-sign',
                'color' => '#F97316',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
