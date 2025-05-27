<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Size;
use App\Models\Specification;

class ProductFiltersSeeder extends Seeder
{
    public function run()
    {
        // Create some brands
        $brands = [
            ['name' => 'Dell', 'description' => 'Dell Computer Corporation'],
            ['name' => 'HP', 'description' => 'Hewlett-Packard'],
            ['name' => 'Lenovo', 'description' => 'Lenovo Group Limited'],
            ['name' => 'Apple', 'description' => 'Apple Inc.'],
            ['name' => 'Samsung', 'description' => 'Samsung Electronics'],
            ['name' => 'Microsoft', 'description' => 'Microsoft Corporation'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Create some categories
        $categories = [
            [
                'name' => 'Laptops',
                'slug' => 'laptops',
                'description' => 'Portable computers',
                'children' => [
                    ['name' => 'Business', 'slug' => 'business-laptops'],
                    ['name' => 'Gaming', 'slug' => 'gaming-laptops'],
                    ['name' => 'Ultrabooks', 'slug' => 'ultrabooks'],
                ]
            ],
            [
                'name' => 'Desktops',
                'slug' => 'desktops',
                'description' => 'Desktop computers',
                'children' => [
                    ['name' => 'All-in-One', 'slug' => 'all-in-one'],
                    ['name' => 'Gaming PCs', 'slug' => 'gaming-pcs'],
                    ['name' => 'Workstations', 'slug' => 'workstations'],
                ]
            ],
            [
                'name' => 'Components',
                'slug' => 'components',
                'description' => 'Computer parts',
                'children' => [
                    ['name' => 'Processors', 'slug' => 'processors'],
                    ['name' => 'Memory', 'slug' => 'memory'],
                    ['name' => 'Storage', 'slug' => 'storage'],
                ]
            ],
        ];

        foreach ($categories as $category) {
            $children = $category['children'] ?? [];
            unset($category['children']);

            $cat = Category::create($category);

            foreach ($children as $child) {
                $child['parent_id'] = $cat->id;
                Category::create($child);
            }
        }

        // Create sizes
        $sizes = [
            ['name' => '13-inch', 'value' => '13"', 'dimension_type' => 'screen'],
            ['name' => '14-inch', 'value' => '14"', 'dimension_type' => 'screen'],
            ['name' => '15-inch', 'value' => '15.6"', 'dimension_type' => 'screen'],
            ['name' => '17-inch', 'value' => '17.3"', 'dimension_type' => 'screen'],
            ['name' => 'Small', 'value' => 'Small', 'dimension_type' => 'case'],
            ['name' => 'Medium', 'value' => 'Medium', 'dimension_type' => 'case'],
            ['name' => 'Large', 'value' => 'Large', 'dimension_type' => 'case'],
        ];

        foreach ($sizes as $size) {
            Size::create($size);
        }

        // Create specifications
        $specs = [
            // Technical group
            ['name' => 'Processor', 'group' => 'Technical'],
            ['name' => 'RAM', 'group' => 'Technical'],
            ['name' => 'Storage', 'group' => 'Technical'],
            ['name' => 'Graphics Card', 'group' => 'Technical'],
            ['name' => 'Operating System', 'group' => 'Technical'],

            // Physical group
            ['name' => 'Weight', 'group' => 'Physical'],
            ['name' => 'Dimensions', 'group' => 'Physical'],
            ['name' => 'Color', 'group' => 'Physical'],
            ['name' => 'Material', 'group' => 'Physical'],

            // Display group
            ['name' => 'Resolution', 'group' => 'Display'],
            ['name' => 'Panel Type', 'group' => 'Display'],
            ['name' => 'Refresh Rate', 'group' => 'Display'],
            ['name' => 'Touch Screen', 'group' => 'Display'],

            // Connectivity
            ['name' => 'USB Ports', 'group' => 'Connectivity'],
            ['name' => 'HDMI', 'group' => 'Connectivity'],
            ['name' => 'DisplayPort', 'group' => 'Connectivity'],
            ['name' => 'WiFi', 'group' => 'Connectivity'],
            ['name' => 'Bluetooth', 'group' => 'Connectivity'],
        ];

        foreach ($specs as $spec) {
            Specification::create($spec);
        }
    }
}
