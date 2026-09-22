@extends('frontend.layout')

@section('title', 'Terms of Service')

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
        <h1>Terms of Service</h1>
        <div class="meta">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    @include('frontend.policies._nav')

    <div class="policy-body">
        <h2>1. Introduction</h2>
        <p>Welcome to Ark &amp; Star Enterprise ("we", "us", or "our"). By accessing or using our website arkandstar.com and placing an order, you agree to be bound by these Terms of Service. If you do not agree, please discontinue use of the site.</p>

        <h2>2. Account Registration</h2>
        <p>To place orders you must create a customer account. You agree to provide accurate, complete and current information at registration and to keep it updated. You are responsible for safeguarding your password and for all activity under your account.</p>

        <h2>3. Orders &amp; Pricing</h2>
        <p>All orders are subject to availability and confirmation of the order price. Prices displayed on the website are inclusive of applicable taxes unless otherwise stated. We reserve the right to:</p>
        <ul>
            <li>Refuse or cancel any order placed for any reason, including errors in pricing or product information.</li>
            <li>Limit the quantity of items purchased per order or per customer.</li>
            <li>Modify pricing at any time without prior notice (does not affect orders already confirmed).</li>
        </ul>

        <h2>4. Payment</h2>
        <p>We accept payments via Paystack (debit/credit card, bank transfer) and Pay-on-Delivery within selected locations. Payment must be received in full before goods are dispatched for prepaid orders. For Pay-on-Delivery orders, you must tender the exact amount or accept the change as provided by the courier.</p>

        <h2>5. Shipping &amp; Delivery</h2>
        <p>Delivery is handled by our partnered logistics providers. Estimated delivery timeframes are stated at checkout and are not guaranteed. Risk of loss passes to you upon delivery to the address provided. Please review our <a href="{{ route('policies.shipping') }}">Shipping Policy</a> for full details.</p>

        <h2>6. Returns &amp; Refunds</h2>
        <p>Eligible items may be returned within 7 days of delivery subject to the conditions in our <a href="{{ route('policies.returns') }}">Return &amp; Refund Policy</a>.</p>

        <h2>7. Product Information</h2>
        <p>We make every effort to display product images and descriptions accurately. However, we do not warrant that product information, colours or specifications are error-free. Slight variations may occur between the displayed image and the physical product.</p>

        <h2>8. Intellectual Property</h2>
        <p>All content on this website, including text, graphics, logos, and images, is the property of Ark &amp; Star Enterprise and is protected by applicable intellectual property laws. You may not copy, reproduce or distribute any content without our prior written permission.</p>

        <h2>9. Limitation of Liability</h2>
        <p>To the fullest extent permitted by law, Ark &amp; Star Enterprise shall not be liable for any indirect, incidental, consequential or punitive damages arising from your use of the website or any product purchased from us. Our total liability shall not exceed the amount paid by you for the specific product giving rise to the claim.</p>

        <h2>10. Modification of Terms</h2>
        <p>We reserve the right to update these Terms at any time. Continued use of the website after changes constitutes acceptance of the revised Terms. Please review this page periodically.</p>

        <h2>11. Governing Law</h2>
        <p>These Terms are governed by the laws of the Federal Republic of Nigeria. Any disputes shall be resolved in the courts of competent jurisdiction located in Lagos, Nigeria.</p>

        <h2>12. Contact</h2>
        <p>For questions regarding these Terms, contact us at <a href="mailto:support@arkandstar.com">support@arkandstar.com</a>.</p>
    </div>
</div>
@endsection