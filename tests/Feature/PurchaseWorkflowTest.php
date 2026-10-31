<?php

namespace Tests\Feature;


use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseProductReturn;
use App\Models\ReturnPurchase;
use App\Models\Account;
use App\Models\Product_Warehouse;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AccountingResult;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_cannot_create_purchases(): void
    {
        $this->post(route('purchases.store'), [])
            ->assertRedirect();
    }

    public function test_purchase_return_reduces_stock_persists_return_line_and_posts_accounting(): void
    {
        $user = User::create([
            'name' => 'Returns Staff', 'email' => 'returns@example.com', 'phone' => '0200000006',
            'password' => bcrypt('password'), 'is_active' => true, 'is_deleted' => false,
        ]);
        $warehouse = Warehouse::create([
            'name' => 'Returns Warehouse', 'address' => 'Accra', 'is_active' => true,
        ]);
        $unit = Unit::create([
            'unit_code' => 'PCS', 'unit_name' => 'Piece', 'operator' => '*',
            'operation_value' => 1, 'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Returnable Product', 'code' => 'RET-001', 'type' => 'standard',
            'barcode_symbology' => 'C128', 'category_id' => 1, 'unit_id' => $unit->id,
            'purchase_unit_id' => $unit->id, 'sale_unit_id' => $unit->id,
            'price' => 150, 'cost' => 100, 'qty' => 5, 'is_active' => true,
        ]);
        $account = Account::create([
            'account_no' => '1000', 'name' => 'Cash', 'initial_balance' => 0,
            'total_balance' => 0, 'is_default' => true, 'is_active' => true,
        ]);
        Product_Warehouse::create([
            'product_id' => $product->id, 'warehouse_id' => $warehouse->id, 'qty' => 5,
        ]);
        $purchase = Purchase::create([
            'reference_no' => 'pr-return-source', 'user_id' => $user->id,
            'warehouse_id' => $warehouse->id, 'supplier_id' => null, 'currency_id' => 1,
            'exchange_rate' => 1, 'item' => 1, 'total_qty' => 5, 'total_discount' => 0,
            'total_tax' => 0, 'total_cost' => 500, 'order_tax_rate' => 0, 'order_tax' => 0,
            'order_discount' => 0, 'shipping_cost' => 0, 'grand_total' => 500,
            'paid_amount' => 0, 'status' => 1, 'payment_status' => 1,
        ]);
        ProductPurchase::create([
            'purchase_id' => $purchase->id, 'product_id' => $product->id, 'qty' => 5,
            'recieved' => 5, 'purchase_unit_id' => $unit->id, 'net_unit_cost' => 100,
            'discount' => 0, 'tax_rate' => 0, 'tax' => 0, 'total' => 500,
        ]);

        $this->mock(AccountingService::class, function ($mock) {
            $mock->shouldReceive('recordPurchaseReturn')
                ->once()
                ->andReturn(new AccountingResult());
        });

        $this->actingAs($user)->post(route('return-purchase.store'), [
            'purchase_id' => $purchase->id,
            'account_id' => $account->id,
            'is_return' => [$product->id],
            'product_id' => [$product->id],
            'product_code' => [$product->code],
            'product_batch_id' => [null],
            'imei_number' => [''],
            'qty' => [2],
            'purchase_unit' => [$unit->unit_name],
            'net_unit_cost' => [100],
            'discount' => [0],
            'tax_rate' => [0],
            'tax' => [0],
            'subtotal' => [200],
            'order_tax_rate' => 0,
            'return_note' => 'Damaged goods',
        ])->assertRedirect('return-purchase');

        $return = ReturnPurchase::firstOrFail();
        $this->assertDatabaseHas('purchase_product_return', [
            'return_id' => $return->id, 'product_id' => $product->id, 'qty' => 2,
        ]);
        $this->assertSame(3.0, (float) $product->fresh()->qty);
        $this->assertSame(3.0, (float) Product_Warehouse::where([
            'product_id' => $product->id, 'warehouse_id' => $warehouse->id,
        ])->value('qty'));

        $this->actingAs($user)->post(route('return-purchase.store'), [
            'purchase_id' => $purchase->id, 'account_id' => $account->id,
            'is_return' => [$product->id], 'product_id' => [$product->id],
            'product_code' => [$product->code], 'product_batch_id' => [null],
            'imei_number' => [''], 'qty' => [4], 'purchase_unit' => [$unit->unit_name],
            'net_unit_cost' => [100], 'discount' => [0], 'tax_rate' => [0],
            'tax' => [0], 'subtotal' => [400], 'order_tax_rate' => 0,
        ])->assertSessionHas('not_permitted');

        $this->assertDatabaseCount('return_purchases', 1);
        $this->assertSame(3.0, (float) $product->fresh()->qty);
    }

    public function test_reject_payment_targets_the_requested_payment_and_updates_purchase(): void
    {
        $user = User::create([
            'name' => 'Approver', 'email' => 'approver@example.com', 'phone' => '0200000005',
            'password' => bcrypt('password'), 'is_active' => true, 'is_deleted' => false,
        ]);
        $account = Account::create([
            'account_no' => '1000', 'name' => 'Cash', 'initial_balance' => 0,
            'total_balance' => 0, 'is_default' => true, 'is_active' => true,
        ]);
        $purchase = Purchase::create([
            'reference_no' => 'pr-test-reject', 'user_id' => $user->id, 'warehouse_id' => 1,
            'supplier_id' => null, 'currency_id' => 1, 'exchange_rate' => 1,
            'item' => 1, 'total_qty' => 1, 'total_discount' => 0, 'total_tax' => 0,
            'total_cost' => 100, 'order_tax_rate' => 0, 'order_tax' => 0,
            'order_discount' => 0, 'shipping_cost' => 0, 'grand_total' => 100,
            'paid_amount' => 0, 'status' => 1, 'payment_status' => 3,
        ]);
        $payment = Payment::create([
            'purchase_id' => $purchase->id, 'user_id' => $user->id, 'account_id' => $account->id,
            'payment_reference' => 'ppr-test-reject', 'amount' => 100, 'change' => 0,
            'paying_method' => 'Cash', 'approval_status' => 'waiting_approval',
        ]);

        $this->actingAs($user)->from('/purchases/' . $purchase->id . '/show')
            ->post(route('purchase.reject-payment', $payment->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'paid_amount' => 0,
            'payment_status' => 4,
        ]);
    }

    public function test_purchase_receipt_updates_stock_persists_tax_and_posts_accounting(): void
    {
        $user = User::create([
            'name' => 'Purchasing Staff',
            'email' => 'purchasing@example.com',
            'phone' => '0200000003',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_deleted' => false,
        ]);

        $warehouse = Warehouse::create([
            'name' => 'Main Warehouse',
            'phone' => '0200000000',
            'email' => 'warehouse@example.com',
            'address' => 'Accra',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'Main Supplier',
            'company_name' => 'Supplier Ltd',
            'email' => 'supplier@example.com',
            'phone_number' => '0200000004',
            'address' => 'Accra',
            'city' => 'Accra',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'unit_code' => 'PCS',
            'unit_name' => 'Piece',
            'operator' => '*',
            'operation_value' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Purchased Product',
            'code' => 'PUR-001',
            'type' => 'standard',
            'barcode_symbology' => 'C128',
            'category_id' => 1,
            'unit_id' => $unit->id,
            'purchase_unit_id' => $unit->id,
            'sale_unit_id' => $unit->id,
            'price' => 150,
            'cost' => 100,
            'qty' => 0,
            'is_active' => true,
        ]);

        $tax = Tax::create([
            'name' => 'VAT',
            'rate' => 15,
            'is_active' => true,
        ]);

        Product_Warehouse::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'qty' => 0,
        ]);

        $this->mock(AccountingService::class, function ($mock) {
            $mock->shouldReceive('recordPurchase')
                ->once()
                ->andReturn(new AccountingResult());
        });

        $response = $this->actingAs($user)->post(route('purchases.store'), [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'currency_id' => 1,
            'exchange_rate' => 1,
            'item' => 1,
            'total_qty' => 2,
            'total_discount' => 0,
            'total_tax' => 30,
            'total_cost' => 200,
            'order_tax_rate' => 15,
            'order_tax' => 30,
            'order_discount' => 0,
            'shipping_cost' => 0,
            'grand_total' => 230,
            'paid_amount' => 0,
            'status' => 1,
            'payment_status' => 1,
            'note' => 'Test purchase',
            'product_id' => [$product->id],
            'product_code' => [$product->code],
            'purchase_unit' => [$unit->unit_name],
            'qty' => [2],
            'recieved' => [2],
            'batch_no' => [''],
            'expired_date' => [''],
            'net_unit_cost' => [100],
            'discount' => [0],
            'tax_rate' => [(string) $tax->id],
            'tax_names' => [$tax->name],
            'tax' => [30],
            'subtotal' => [230],
            'imei_number' => [''],
        ]);

        $response->assertRedirect('purchases');

        $purchase = \App\Models\Purchase::firstOrFail();
        $line = ProductPurchase::where('purchase_id', $purchase->id)->firstOrFail();

        $this->assertSame(15.0, (float) $line->tax_rate);
        $this->assertSame(2.0, (float) $line->recieved);
        $this->assertSame(2.0, (float) $product->fresh()->qty);
        $this->assertSame(2.0, (float) Product_Warehouse::where([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
        ])->value('qty'));
    }
}
