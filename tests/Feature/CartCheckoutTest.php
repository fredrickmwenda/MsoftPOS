<?php

namespace Tests\Feature;

use App\Models\Biller;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_customer_sign_in_before_using_the_cart_or_checkout(): void
    {
        $this->from('/shop')->post('/cart/add/1')
            ->assertRedirect(route('customer.login'));

        $this->post('/checkout', [])->assertRedirect(route('customer.login'));
        $this->get('/cart')->assertRedirect(route('customer.login'));

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_signed_in_customer_can_place_pay_on_delivery_order_with_delivery_details(): void
    {
        User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'),
            'phone' => '0200000002', 'is_active' => true, 'is_deleted' => false,
        ]);
        Warehouse::create([
            'name' => 'Main Warehouse', 'phone' => '0200000000',
            'email' => 'warehouse@example.com', 'address' => 'Accra', 'is_active' => true,
        ]);
        Account::create([
            'account_no' => '1000', 'name' => 'Cash', 'initial_balance' => 0,
            'total_balance' => 0, 'is_default' => true, 'is_active' => true,
        ]);
        $customer = Customer::create([
            'customer_group_id' => 1, 'name' => 'Jane Customer',
            'email' => 'jane@example.com', 'phone_number' => '+233241234567',
            'address' => 'Tema Community 1', 'city' => 'Tema', 'state' => 'Greater Accra',
            'postal_code' => '00233', 'country' => 'Ghana', 'is_active' => true,
            'password' => bcrypt('password'),
        ]);
        Biller::create([
            'name' => 'Main Biller', 'company_name' => 'JoexPOS',
            'email' => 'biller@example.com', 'phone_number' => '0200000001',
            'address' => 'Accra', 'city' => 'Accra', 'state' => 'Greater Accra',
            'postal_code' => '00233', 'country' => 'Ghana', 'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Sample Product', 'code' => 'SP-001', 'type' => 'standard',
            'barcode_symbology' => 'C128', 'category_id' => 1, 'unit_id' => 1,
            'purchase_unit_id' => 1, 'sale_unit_id' => 1,
            'price' => 150, 'cost' => 100, 'qty' => 20, 'is_active' => true,
        ]);

        $this->actingAs($customer, 'customer')->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => $product->price,
                'quantity' => 2, 'image' => 'https://example.com/image.jpg'],
        ]])->post('/checkout', [
            'name' => 'Jane Customer', 'phone_number' => '+233241234567',
            'email' => 'jane@example.com', 'delivery_address' => 'Tema Community 1',
            'order_notes' => 'Handle with care', 'payment_method' => 'delivery',
        ])->assertRedirect(route('customer.orders.confirmation', Sale::first()));

        $this->assertDatabaseHas('sales', [
            'customer_id' => $customer->id, 'payment_status' => 1,
            'paid_amount' => 0, 'grand_total' => 300,
        ]);
        $this->assertDatabaseHas('product_sales', ['product_id' => $product->id, 'qty' => 2]);
        $this->assertDatabaseHas('deliveries', [
            'sale_id' => Sale::first()->id, 'address' => 'Tema Community 1', 'status' => 1,
        ]);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id, 'phone_number' => '+233241234567', 'address' => 'Tema Community 1',
        ]);
        $this->assertSame(18, (int) $product->fresh()->qty);
    }

    public function test_paystack_order_is_marked_paid_only_after_matching_gateway_verification(): void
    {
        User::create([
            'name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'),
            'phone' => '0200000002', 'is_active' => true, 'is_deleted' => false,
        ]);
        Warehouse::create([
            'name' => 'Main Warehouse', 'phone' => '0200000000',
            'email' => 'warehouse@example.com', 'address' => 'Accra', 'is_active' => true,
        ]);
        Account::create([
            'account_no' => '1000', 'name' => 'Cash', 'initial_balance' => 0,
            'total_balance' => 0, 'is_default' => true, 'is_active' => true,
        ]);
        $customer = Customer::create([
            'customer_group_id' => 1, 'name' => 'Jane Customer',
            'email' => 'jane@example.com', 'phone_number' => '+233241234567',
            'address' => 'Tema Community 1', 'city' => 'Tema', 'state' => 'Greater Accra',
            'postal_code' => '00233', 'country' => 'Ghana', 'is_active' => true,
            'password' => bcrypt('password'),
        ]);
        Biller::create([
            'name' => 'Main Biller', 'company_name' => 'JoexPOS',
            'email' => 'biller@example.com', 'phone_number' => '0200000001',
            'address' => 'Accra', 'city' => 'Accra', 'state' => 'Greater Accra',
            'postal_code' => '00233', 'country' => 'Ghana', 'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Sample Product', 'code' => 'SP-001', 'type' => 'standard',
            'barcode_symbology' => 'C128', 'category_id' => 1, 'unit_id' => 1,
            'purchase_unit_id' => 1, 'sale_unit_id' => 1,
            'price' => 150, 'cost' => 100, 'qty' => 20, 'is_active' => true,
        ]);

        config(['services.paystack.secret' => 'test-secret', 'services.paystack.public' => 'test-public']);
        $reference = null;
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => function ($request) use (&$reference) {
                $reference = $request['reference'];
                return Http::response(['status' => true, 'data' => ['authorization_url' => 'https://paystack.test/authorize']]);
            },
            'https://api.paystack.co/transaction/verify/*' => function () use (&$reference) {
                return Http::response(['status' => true, 'data' => [
                    'status' => 'success', 'reference' => $reference, 'amount' => 30000, 'currency' => 'GHS',
                ]]);
            },
        ]);

        $this->actingAs($customer, 'customer')->withSession(['cart' => [
            $product->id => ['name' => $product->name, 'price' => $product->price,
                'quantity' => 2, 'image' => 'https://example.com/image.jpg'],
        ]])->post('/checkout', [
            'name' => 'Jane Customer', 'phone_number' => '+233241234567',
            'email' => 'jane@example.com', 'delivery_address' => 'Tema Community 1',
            'payment_method' => 'paystack',
        ])->assertRedirect('https://paystack.test/authorize');

        $this->assertNotNull($reference);
        $sale = Sale::firstOrFail();
        $this->assertSame(1, (int) $sale->payment_status);

        $this->get(route('checkout.paystack.callback', ['reference' => $reference]))
            ->assertRedirect(route('customer.orders.confirmation', $sale));

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'payment_status' => 4, 'paid_amount' => 300]);
        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'payment_reference' => $reference, 'amount' => 300]);
        $this->assertSame(18, (int) $product->fresh()->qty);
    }
}
