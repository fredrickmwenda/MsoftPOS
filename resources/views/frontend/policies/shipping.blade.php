@extends('frontend.layout')

@section('title', 'Shipping Policy')

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
.policy-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
.policy-table th, .policy-table td { border: 1px solid #e5e7eb; padding: 10px 14px; text-align: left; }
.policy-table th { background: #f9fafb; color: #1f2937; font-weight: 600; }
</style>

<div class="policy-wrap">
    <div class="policy-head">
        <h1>Shipping Policy</h1>
        <div class="meta">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    @include('frontend.policies._nav')

    <div class="policy-body">
        <h2>1. Order Processing Time</h2>
        <p>Orders are processed within 1–2 business days (Monday–Friday, excluding public holidays). Orders placed after 12:00 PM on Friday, or on weekends, will be processed on the next business day.</p>

        <h2>2. Delivery Areas</h2>
        <p>We currently deliver to all states in the Federal Republic of Nigeria. International shipping is not available at this time.</p>

        <h2>3. Shipping Methods &amp; Timeframes</h2>
        <table class="policy-table">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Estimated Delivery</th>
                    <th>Service Areas</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Standard Delivery</td>
                    <td>3–5 business days</td>
                    <td>Nationwide</td>
                </tr>
                <tr>
                    <td>Express Delivery</td>
                    <td>1–2 business days</td>
                    <td>Lagos &amp; Abuja only</td>
                </tr>
                <tr>
                    <td>Same-Day Delivery</td>
                    <td>Within 6 hours</td>
                    <td>Select Lagos areas (order before 10 AM)</td>
                </tr>
            </tbody>
        </table>
        <p>Delivery timeframes are estimates and are not guaranteed. External factors such as weather, traffic, or courier delays may affect delivery.</p>

        <h2>4. Shipping Costs</h2>
        <p>Shipping costs are calculated at checkout based on the delivery location, package weight and selected shipping method. Free standard shipping applies to orders above ₦50,000 delivered within Lagos.</p>

        <h2>5. Order Tracking</h2>
        <p>Once your order has been dispatched, you will receive an email and SMS containing a tracking number and a link to track your delivery status online.</p>

        <h2>6. Delivery Attempt &amp; Failed Delivery</h2>
        <p>Our courier will attempt delivery up to two times. If delivery is unsuccessful after two attempts, the order will be returned to our warehouse. We will contact you to arrange re-delivery or cancellation. A re-delivery fee may apply.</p>

        <h2>7. Incorrect Address</h2>
        <p>Please ensure the delivery address provided is accurate and complete. Ark &amp; Star Enterprise is not liable for orders delivered to an incorrect address supplied by the customer. Re-shipping charges will apply if the package is returned to us due to an incorrect address.</p>

        <h2>8. Order Delays</h2>
        <p>We are not responsible for delays caused by the courier, customs inspections, weather, public holidays, or force majeure events. We will, however, work to keep you informed throughout the process.</p>

        <h2>9. Order Cutoff</h2>
        <p>Orders placed after 12:00 PM may be processed on the following business day. Cut-off times may vary during promotional events; please refer to the announcement banner for any temporary changes.</p>

        <h2>10. Contact</h2>
        <p>Shipping inquiries: <a href="mailto:shipping@arkandstar.com">shipping@arkandstar.com</a>.</p>
    </div>
</div>
@endsection