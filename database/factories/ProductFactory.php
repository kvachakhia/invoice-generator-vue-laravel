<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'item_code' => 'IC-1000' . rand(10, 500),
            'description' => 'Description of product-' . rand(10, 500),
            'unit_price' => mt_rand(100, 1000)
        ];
    }
}
