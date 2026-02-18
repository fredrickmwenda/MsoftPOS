# Hire Purchase Workflow - UPDATED

## Overview
The hire purchase system now implements a proper two-stage workflow:
1. **Stage 1**: Down payment collection (immediate)
2. **Stage 2**: Installment creation and payment (after down payment confirmed)

---

## Updated Workflow

### Stage 1: Sale Creation with Down Payment

**User Action**: Create a new sale and enable hire purchase
- Checkbox: "Is Hire Purchase"
- Down Payment Amount: (minimum 10% of total)
- Terms: Select 3, 6, 12, or 24 months
- System auto-calculates interest rate and monthly installment

**What Happens**:
```php
// In SaleController@store()
if(isset($data['is_hire_purchase']) && $data['is_hire_purchase']) {
    // Validate hire purchase data
    $hirePurchaseService->validateHirePurchase($data);
    
    // Set status to 'pending' - installments NOT created yet
    $lims_sale_data->hire_purchase_status = 'pending';
    $lims_sale_data->save();
    
    // NO installments created at this stage!
}
```

**Sale State After Creation**:
- `hire_purchase_status` = 'pending'
- `hire_purchase_down_payment` = Amount set by user
- `hire_purchase_installments` = EMPTY (not yet created)
- User records down payment as a normal payment transaction
- `paid_amount` updated with down payment amount

---

### Stage 2: Down Payment Confirmation → Installment Creation

**When Down Payment is Recorded**:
1. User clicks money icon (💰) in hire purchase table
2. System fetches installments via `getInstallments()` AJAX endpoint
3. Since status is 'pending', returns: `{ status: 'pending_down_payment', down_payment_amount: ... }`
4. Modal shows message: "Awaiting Down Payment Confirmation"
5. User records the down payment using the normal payment system

**After Down Payment Confirmed**:
1. Call: `POST /hire-purchase/process-down-payment?sale_id=X`
2. System validates that `hire_purchase_status = 'pending'`
3. Calls `HirePurchaseService::createInstallments($sale)`
4. Creates all installment records in `hire_purchase_installments` table
5. Updates `hire_purchase_status = 'active'`
6. Clears cache for this sale

```php
// In HirePurchaseController@processDownPayment()
if ($sale->hire_purchase_status === 'pending') {
    $this->hirePurchaseService->createInstallments($sale);
    $sale->hire_purchase_status = 'active';
    $sale->save();
    cache()->forget('hire_purchase_' . $sale->id);
}
```

---

### Stage 3: Record Installment Payments

**Now the system is ready for installment payments**:
1. Click money icon (💰) again
2. Modal loads pending installments (because status is now 'active')
3. Display first unpaid installment with:
   - Installment number
   - Due date
   - Amount due
   - Amount already paid
   - Remaining amount
4. User enters payment amount, date, method, and notes
5. System records payment against the installment

```php
// In HirePurchaseController@recordPayment()
// AJAX endpoint to record individual installment payment
$installment->paid_amount += $paymentAmount;
if ($installment->paid_amount >= $installment->amount) {
    $installment->payment_status = 'paid';
}
$installment->save();
```

---

## API Response Examples

### getInstallments() - Pending Down Payment
```json
{
    "error": "Awaiting down payment confirmation. Once down payment is recorded, installments will be created.",
    "status": "pending_down_payment",
    "down_payment_amount": 5000.00
}
```
**HTTP Status**: 202 Accepted (not an error, just waiting)

### getInstallments() - Active with Pending Installments
```json
{
    "installments": [
        {
            "id": 1,
            "sale_id": 123,
            "installment_number": 1,
            "due_date": "2025-12-23",
            "amount": 2500.00,
            "paid_amount": 0,
            "remaining_amount": 2500.00,
            "payment_status": "unpaid"
        }
    ],
    "customer_name": "John Doe",
    "total_terms": 6
}
```
**HTTP Status**: 200 OK

### getInstallments() - All Paid
```json
{
    "error": "No pending installments. All installments have been paid.",
    "status": "all_paid"
}
```
**HTTP Status**: 200 OK

### processDownPayment() - Success
```json
{
    "success": true,
    "message": "Down payment confirmed. Installments created successfully.",
    "installments_count": 6
}
```
**HTTP Status**: 200 OK

---

## Database Schema

### sales table
```
| Field                          | Type    | Notes                        |
|--------------------------------|---------|------------------------------|
| is_hire_purchase               | boolean | Enable hire purchase         |
| hire_purchase_down_payment     | decimal | Amount paid as down payment  |
| hire_purchase_terms            | integer | 3, 6, 12, or 24 months      |
| hire_purchase_interest_rate    | decimal | Auto-calculated per terms    |
| hire_purchase_status           | string  | pending → active → completed |
| hire_purchase_start_date       | date    | When installments begin      |
| hire_purchase_end_date         | date    | When final payment due       |
```

### hire_purchase_installments table
```
| Field              | Type      | Notes                        |
|--------------------|-----------|------------------------------|
| id                 | bigint    | Primary key                  |
| sale_id            | bigint FK | Link to sales table          |
| installment_number | integer   | 1, 2, 3, ... N              |
| due_date           | date      | Monthly payment due date     |
| amount             | decimal   | Monthly installment amount   |
| paid_amount        | decimal   | Amount paid so far           |
| payment_status     | string    | unpaid → partial → paid     |
| payment_date       | date      | When payment was recorded    |
| payment_method     | string    | cash, card, check, etc       |
| notes              | text      | Payment notes                |
| created_at         | timestamp | Record creation time         |
| updated_at         | timestamp | Last update time             |
```

---

## UI Flow

### Hire Purchase Index Page

#### Before Down Payment is Recorded
```
┌─────────────────────────────────────────────────┐
│ Reference: ORD-001                              │
│ Customer: John Doe                              │
│ Amount: $50,000                                 │
│ Down Payment: $5,000                            │
│ Status: [PENDING]  [View] [💰 Record]          │
└─────────────────────────────────────────────────┘

Click [💰 Record] → Modal Opens:
┌──────────────────────────────────────────────┐
│ Reference: ORD-001                           │
│ ⚠️ Awaiting Down Payment                     │
│ Down Payment Required: $5,000                │
│ (Once recorded, installments will appear)    │
│                                              │
│ [Cancel]                                     │
└──────────────────────────────────────────────┘
```

#### After Down Payment is Recorded (processDownPayment called)
```
┌─────────────────────────────────────────────────┐
│ Reference: ORD-001                              │
│ Customer: John Doe                              │
│ Amount: $50,000                                 │
│ Down Payment: $5,000 ✓ Received                │
│ Status: [ACTIVE]  [View] [💰 Record]          │
└─────────────────────────────────────────────────┘

Click [💰 Record] → Modal Opens:
┌──────────────────────────────────────────────────┐
│ Reference: ORD-001                               │
│ Installment #1 of 6                              │
│ Due Date: 2025-12-23                             │
│                                                  │
│ Customer: John Doe                               │
│ Installment Amount: $9,000.00                    │
│ Already Paid: $0.00                              │
│ Remaining: $9,000.00                             │
│                                                  │
│ Amount to Pay: [________]                        │
│ Payment Date: [____/____/____]                   │
│ Payment Method: [Cash ▼]                         │
│ Payment Notes: [_________________]               │
│                                                  │
│ [Cancel] [Record Payment]                        │
└──────────────────────────────────────────────────┘
```

---

## Key Changes from Previous Implementation

| Aspect | Before | After |
|--------|--------|-------|
| **Installment Creation** | Immediate during sale creation | After down payment confirmation |
| **Sale Status** | N/A | pending → active → completed |
| **Modal Behavior** | Error if no installments | Shows "Awaiting Down Payment" message |
| **Payment Flow** | Direct to installments | Down payment first, then installments |
| **getInstallments() Response** | 404 if no records | 202 with waiting message if pending |
| **Trigger for Installments** | Automatic in store() | Manual via processDownPayment() |

---

## Implementation Notes

### For Payment Processing Integration
When the down payment is recorded via the normal payment system:
1. A Payment record is created
2. `paid_amount` on Sale is updated
3. After payment is confirmed, call `processDownPayment()` to trigger installment creation

### Example Integration
```php
// In Payment creation/confirmation flow:
if ($sale->is_hire_purchase && $sale->hire_purchase_status === 'pending') {
    // After payment is successfully recorded
    $response = Http::post(route('hire_purchase.process_down_payment'), [
        'sale_id' => $sale->id
    ]);
}
```

---

## Testing Checklist

- [ ] Create hire purchase sale with down payment enabled
- [ ] Verify `hire_purchase_status` = 'pending' in database
- [ ] Click payment button, verify "Awaiting Down Payment" message
- [ ] Record down payment as normal payment
- [ ] Call `processDownPayment()` endpoint
- [ ] Verify installments are created (check `hire_purchase_installments` table)
- [ ] Verify `hire_purchase_status` = 'active'
- [ ] Click payment button again, verify first installment appears
- [ ] Record installment payment
- [ ] Verify `paid_amount` and `payment_status` updated correctly

---

## Routes

```
GET  /hire-purchase/                         # List all hire purchase sales
GET  /hire-purchase/dashboard                # Dashboard/summary
GET  /hire-purchase/pending                  # All pending installments
GET  /hire-purchase/overdue                  # All overdue installments
GET  /hire-purchase/show/{id}                # View hire purchase details
GET  /hire-purchase/get-installments         # AJAX: Get pending installments
POST /hire-purchase/process-down-payment     # AJAX: Create installments after down payment
POST /hire-purchase/record-payment/{id}      # AJAX: Record installment payment
GET  /hire-purchase/report                   # Generate report
POST /hire-purchase/export-report            # Export report
```

