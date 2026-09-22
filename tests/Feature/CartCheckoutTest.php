<?php

namespace Tests\Feature;

use App\Models\Biller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_on_delivery_checkout_creates_sale_record(): void
    {
        Warehouse::create([
            'name' => 'Main Warehouse',
            'phone' => '0200000000',
            'email' => 'warehouse@example.com',
            'address' => 'Accra',
            'is_active' => true,
        ]);

        Biller::create([
            'name' => 'Main Biller',
            'company_name' => 'JoexPOS',
            'email' => 'biller@example.com',
            'phone_number' => '0200000001',
            'address' => 'Accra',
            'city' => 'Accra',
            'state' => 'Greater Accra',
            'postal_code' => '00233',
            'country' => 'Ghana',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Sample Product',
            'code' => 'SP-001',
            'type' => 'standard',
            'price' => 150,
            'cost' => 100,
            'qty' => 20,
            'is_active' => true,
        ]);

        $this->withSession([
            'cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => 2,
                    'image' => 'https://example.com/image.jpg',
                ],
            ],
        ])->post('/checkout', [
            'name' => 'Jane Customer',
            'phone_number' => '+233241234567',
            'email' => 'jane@example.com',
            'delivery_address' => 'Tema Community 1',
            'order_notes' => 'Handle with care',
            'payment_method' => 'delivery',
        ])->assertRedirect(route('shop.index'));

        $this->assertDatabaseHas('sales', [
            'customer_id' => Customer::first()->id,
            'payment_status' => 1,
            'grand_total' => 300,
        ]);

        $this->assertDatabaseHas('product_sales', [
            'product_id' => $product->id,
            'qty' => 2,
        ]);
    }
}
