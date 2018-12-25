<?php

use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = factory(\App\Models\Product::class, 50)->create();

        foreach ($products as $product) {
            $sku = factory(\App\Models\ProductSku::class, 4)->create(['product_id' => $product->id]);

            $product->update(['price' => $sku->min('price')]);
        }
    }
}
