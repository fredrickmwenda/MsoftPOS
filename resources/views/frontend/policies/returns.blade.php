@extends('frontend.layout')

@section('title', 'Return & Refund Policy')

@section('content')
<style>
.policy-wrap { max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; color: #374151; }
.policy-head { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 35px; margin-bottom: 25px; }
.policy-head h1 { font-size: 28px; color: #1f2937; margin-bottom: 6px; }
.policy-head .meta { font-size: 13px; color: #6b7280; }
.policy-nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
.policy-nav a { padding: 8px 16px; background: #fff; border: 1px solid #e5e7eb; border-radius: 20px; text-decoration: none; font-size: 13px; color: #4b5563; font-weight: 500; }
.policy-nav a.active, .policy-nav a:hover { background: #16a34a; color: #fff; border-color: #16a34a; }
.policy-body { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 35px 40px; line-height: 1.7; }
.policy-body h2 { font-size: 18px; color: #1f2937; margin: 25px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #f3f4f6; }
.policy-body h2:first-child { margin-top: 0; }
.policy-body p { margin-bottom: 12px; font-size: 14.5px; }
.policy-body ul { margin: 10px 0 15px 22px; }
.policy-body li { margin-bottom: 6px; font-size: 14.5px; }
.policy-body a { color: #16a34a; }
.callout { background: #f0fdf4; border-left: 4px solid #16a34a; padding: 15px 20px; border-radius: 6px; margin: 15px 0; font-size: 14px; }
</style>

<div class="policy-wrap">
    <div class="policy-head">
        <h1>Return &amp; Refund Policy</h1>
        <div class="meta">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    @include('frontend.policies._nav')

    <div class="policy-body">
        <div class="callout">
            <strong>Summary:</strong> You may return eligible items within <strong>7 days</strong> of delivery for a full refund, provided they are unused, in original packaging and accompanied by proof of purchase.
        </div>

        <h2>1. Return Eligibility</h2>
        <p>To be eligible for a return, your item must satisfy all of the following:</p>
        <ul>
            <li>Unused and in the same condition that you received it.</li>
            <li>In the original packaging with tags, manuals and accessories intact.</li>
            <li>Accompanied by the original receipt or order reference number.</li>
        </ul>

        <h2>2. Non-Returnable Items</h2>
        <p>Certain items cannot be returned, including:</p>
        <ul>
            <li>Perishable goods, food items and consumables.</li>
            <li>Personal care items (e.g., cosmetics, perfumes, underwear) for hygiene reasons.</li>
            <li>Customised or personalised products.</li>
            <li>Gift cards and downloadable software.</li>
            <li>Items marked "Final Sale" or "Clearance".</li>
        </ul>

        <h2>3. How to Initiate a Return</h2>
        <p>To start a return, email <a href="mailto:returns@arkandstar.com">returns@arkandstar.com</a> with your order reference number and reason for return. Once approved, we will provide a return address and instructions. Do not send items back without first requesting a return.</p>

        <h2>4. Refund Processing</h2>
        <p>Once we receive and inspect the returned item, we will notify you of the approval or rejection of your refund. If approved:</p>
        <ul>
            <li>Refunds to the original payment method take 5–10 business days.</li>
            <li>Pay-on-Delivery orders will be refunded via bank transfer to an account you provide.</li>
            <li>You will receive an email confirmation once the refund has been issued.</li>
        </ul>

        <h2>5. Late or Missing Refunds</h2>
        <p>If you haven't received a refund yet, please check your bank account again, then contact your bank or card issuer as processing times vary. If you've done this and still have not received your refund, contact us at <a href="mailto:support@arkandstar.com">support@arkandstar.com</a>.</p>

        <h2>6. Exchanges</h2>
        <p>We only replace items if they are defective or damaged on arrival. To request an exchange, email us within 48 hours of delivery with photo evidence of the damage.</p>

        <h2>7. Damaged or Defective Items</h2>
        <p>If you receive a damaged or defective item, please contact us within 48 hours of delivery with your order number and a clear photo of the product and packaging. We will arrange a free replacement or full refund at our discretion.</p>

        <h2>8. Shipping Costs for Returns</h2>
        <p>Return shipping costs are the responsibility of the customer unless the return is due to our error (wrong item shipped, or product arrived damaged). In such cases we will cover return shipping.</p>

        <h2>9. Refund Timeframe</h2>
        <p>Approved refunds are typically issued within 5–10 business days of receiving the returned item. Bank processing times may extend this period beyond our control.</p>

        <h2>10. Contact</h2>
        <p>For any return-related questions, email <a href="mailto:returns@arkandstar.com">returns@arkandstar.com</a>.</p>
    </div>
</div>
@endsection