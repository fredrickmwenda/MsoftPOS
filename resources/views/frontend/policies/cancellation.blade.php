@extends('frontend.layout')

@section('title', 'Cancellation Policy')

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
</style>

<div class="policy-wrap">
    <div class="policy-head">
        <h1>Cancellation Policy</h1>
        <div class="meta">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    @include('frontend.policies._nav')

    <div class="policy-body">
        <h2>1. Customer-Initiated Cancellations</h2>
        <p>You may cancel an order at no charge provided the order has not yet been dispatched from our warehouse. To cancel, contact us immediately at <a href="mailto:support@arkandstar.com">support@arkandstar.com</a> with your order reference number.</p>

        <h2>2. Cancellation Timeframe</h2>
        <p>Cancellation requests must be received within:</p>
        <ul>
            <li><strong>2 hours</strong> of placing the order for Same-Day or Express delivery options.</li>
            <li><strong>12 hours</strong> of placing the order for Standard delivery.</li>
        </ul>
        <p>Requests received after these windows may not be actionable if the order has already been processed or handed to the courier.</p>

        <h2>3. Order Modification</h2>
        <p>Minor modifications (delivery address, contact phone) can be made before dispatch by contacting support. Changes to product, size, colour or quantity require cancelling the original order and placing a new one.</p>

        <h2>4. Seller-Initiated Cancellations</h2>
        <p>We reserve the right to cancel an order for any of the following reasons:</p>
        <ul>
            <li>Item is out of stock or unavailable.</li>
            <li>Pricing or product information error on the website.</li>
            <li>Suspected fraudulent or unauthorised transaction.</li>
            <li>Limitations on quantities purchased per customer.</li>
            <li>Inability to deliver to the address provided.</li>
        </ul>
        <p>In such cases, a full refund will be issued to the original payment method within 5–10 business days.</p>

        <h2>5. Refund on Cancellation</h2>
        <p>Approved cancellations will be refunded in full within 5–10 business days. Pay-on-Delivery orders require bank details to process a transfer refund.</p>

        <h2>6. Cancellation After Dispatch</h2>
        <p>Once an order has been dispatched, cancellation is no longer possible. You may, however, refuse delivery or initiate a return once the order has been received, in line with our <a href="{{ route('policies.returns') }}">Return &amp; Refund Policy</a>.</p>

        <h2>7. Contact</h2>
        <p>For cancellations, email <a href="mailto:support@arkandstar.com">support@arkandstar.com</a> or call our customer care line during business hours.</p>
    </div>
</div>
@endsection