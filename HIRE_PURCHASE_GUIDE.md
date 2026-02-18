# Hire Purchase System Implementation Guide

## Overview
A complete hire purchase/installment payment system has been integrated into the JoexPOS system. Customers can now purchase items on a hire purchase basis with flexible payment terms and automatic interest calculation.

## Key Features

### 1. **Flexible Payment Terms**
- 3 Months @ 5% Interest
- 6 Months @ 8% Interest
- 12 Months @ 12% Interest
- 24 Months @ 15% Interest

### 2. **Smart Calculations**
- Automatic monthly installment calculation
- Total interest calculation
- Remaining balance tracking
- Down payment validation (minimum 10%)

### 3. **Installment Tracking**
- Automatic installment schedule generation
- Individual payment tracking per installment
- Payment status management (pending, partial, paid)
- Overdue installment detection

### 4. **Dashboard & Reporting**
- Contract overview dashboard
- Pending installments view
- Overdue installments alerts
- Comprehensive reporting with export (CSV, PDF)
- Recent payment tracking

## Database Schema

### Sales Table Additions
```sql
ALTER TABLE sales ADD (
    is_hire_purchase BOOLEAN DEFAULT FALSE,
    hire_purchase_down_payment DECIMAL(15,6) DEFAULT 0,
    hire_purchase_terms INTEGER,
    hire_purchase_interest_rate DECIMAL(5,2) DEFAULT 0,
    hire_purchase_status VARCHAR(255) DEFAULT 'active',
    hire_purchase_start_date DATE,
    hire_purchase_end_date DATE
);
```

### New Table: hire_purchase_installments
```sql
CREATE TABLE hire_purchase_installments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sale_id BIGINT NOT NULL,
    installment_number INT NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(15,6) NOT NULL,
    paid_amount DECIMAL(15,6) DEFAULT 0,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_date DATE,
    payment_method VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);
```

## Files Created/Modified

### New Files Created:
1. **Models:**
   - `app/Models/HirePurchaseInstallment.php` - Installment data model

2. **Services:**
   - `app/Services/HirePurchaseService.php` - Business logic for hire purchase

3. **Controllers:**
   - `app/Http/Controllers/HirePurchaseController.php` - Routes handling

4. **Observers:**
   - `app/Observers/HirePurchaseInstallmentObserver.php` - Cache invalidation

5. **Migrations:**
   - `database/migrations/2025_11_21_081707_add_hire_purchase_to_sales_table.php`
   - `database/migrations/2025_11_22_000000_create_hire_purchase_installments_table.php`

6. **Views:**
   - `resources/views/backend/hire_purchase/dashboard.blade.php` - Dashboard
   - `resources/views/backend/hire_purchase/index.blade.php` - Contract list
   - `resources/views/backend/hire_purchase/show.blade.php` - Contract details
   - `resources/views/backend/hire_purchase/payment_form.blade.php` - Payment recording
   - `resources/views/backend/hire_purchase/pending.blade.php` - Pending installments
   - `resources/views/backend/hire_purchase/overdue.blade.php` - Overdue alerts
   - `resources/views/backend/hire_purchase/report.blade.php` - Reporting

### Modified Files:
1. **`app/Models/Sale.php`** 
   - Added hire purchase fields to fillable array
   - Added hire_purchase_installments() relationship
   - Added accessor methods for calculations

2. **`app/Http/Controllers/SaleController.php`**
   - Added hire purchase logic to store() method
   - Validates and creates installments on sale creation

3. **`app/Providers/AppServiceProvider.php`**
   - Registered HirePurchaseInstallment observer

4. **`routes/web.php`**
   - Added hire purchase routes

5. **`resources/views/backend/sale/create.blade.php`**
   - Added hire purchase UI section
   - Added JavaScript for hire purchase calculations

## How to Use

### 1. Enable Hire Purchase During Sale Creation
1. Open sale creation form (Pos/Sales > Create Sale)
2. Check "Enable Hire Purchase" checkbox
3. Enter down payment (minimum 10% of total)
4. Select payment terms (3, 6, 12, or 24 months)
5. Interest rate auto-calculates based on term
6. Installment schedule displays automatically
7. Save the sale - installments are created automatically

### 2. View Hire Purchase Dashboard
- Navigate to: Hire Purchase > Dashboard
- See overview of:
  - Active contracts
  - Completed contracts
  - Overdue installments
  - Recent payments
  - Upcoming due installments

### 3. Manage Contracts
- View all contracts: Hire Purchase > All Contracts
- Filter by status or customer
- Click on contract to see full details
- View all installments with payment status

### 4. Record Payments
1. Go to Hire Purchase > Pending Installments
2. Click "Record Payment" button
3. Enter payment amount (can be partial)
4. Select payment method and date
5. Add notes if needed
6. Submit - payment is recorded and status updates

### 5. Monitor Overdue Installments
- Navigate to: Hire Purchase > Overdue
- See all overdue payments with:
  - Days overdue
  - Customer contact info
  - Remaining amount
  - Quick payment action

### 6. Generate Reports
- Navigate to: Hire Purchase > Reports
- Select date range
- View contract summary
- Export to CSV or PDF

## API Methods (Service Class)

### HirePurchaseService Methods

```php
// Validate hire purchase data
$service->validateHirePurchase($data);

// Calculate financial details
$service->calculateDetails($grandTotal, $downPayment, $terms);

// Generate installment schedule
$service->generateInstallments($sale, $startDate);

// Create installment records
$service->createInstallments($sale, $startDate);

// Get pending installments
$service->getPendingInstallments($sale);

// Get overdue installments
$service->getOverdueInstallments($customerId);

// Record payment
$service->recordPayment($installment, $amount, $method, $notes);

// Get dashboard summary
$service->getDashboardSummary();
```

## Model Attributes

### Sale Model Accessors
```php
// Get total with interest included
$sale->hire_purchase_total_with_interest

// Get monthly installment amount
$sale->hire_purchase_monthly_installment

// Get remaining balance
$sale->hire_purchase_remaining_balance

// Get complete details array
$sale->hire_purchase_details
```

### HirePurchaseInstallment Accessors
```php
// Get remaining amount for installment
$installment->remaining_amount

// Get days overdue
$installment->days_overdue

// Get status badge color
$installment->status_badge
```

## Cache Invalidation

The system uses automatic cache invalidation via Observers:
- When installments are created/updated/deleted
- Related sale cache is invalidated
- Hire purchase specific caches are cleared
- Dashboard cache is refreshed

## Migration Instructions

```bash
# Run migrations
php artisan migrate

# If you need to rollback
php artisan migrate:rollback --step=2
```

## Testing the System

1. **Create a sale with hire purchase:**
   - Go to POS/Sales > Create Sale
   - Add products
   - Check "Enable Hire Purchase"
   - Enter 10% down payment
   - Select 6-month term
   - Save

2. **Verify installments were created:**
   - Navigate to Hire Purchase > All Contracts
   - Click on the sale reference
   - Verify 6 installments appear with correct dates and amounts

3. **Record a payment:**
   - Go to Hire Purchase > Pending Installments
   - Click "Record Payment" on first installment
   - Enter amount and method
   - Verify payment is recorded

4. **Check dashboard:**
   - Navigate to Hire Purchase > Dashboard
   - Verify counts and recent payments display

## Troubleshooting

### Installments not creating
- Verify hire_purchase_down_payment >= 10% of grand_total
- Check that hire_purchase_terms is in [3, 6, 12, 24]
- Review application logs for errors

### Payment not recording
- Verify amount <= remaining balance
- Check payment_date is valid
- Ensure user has update permission

### Cache issues
- Clear cache: `php artisan cache:clear`
- If specific cache issues: `php artisan cache:forget hire_purchase_list`

## Future Enhancements

- SMS/Email notifications for due dates
- Late payment charges
- Contract amendment/renegotiation
- Automatic payment reminders
- Integration with payment gateways
- PDF invoice generation with installment schedule
- Customer portal for installment tracking
- Statistical analysis and forecasting

## Support & Maintenance

For issues or questions:
1. Check application logs: `storage/logs/laravel.log`
2. Verify database migrations ran successfully
3. Clear cache and reset permissions
4. Review related GitHub issues or documentation
