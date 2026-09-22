@extends('frontend.layout')

@section('title', 'Privacy Policy')

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
        <h1>Privacy Policy</h1>
        <div class="meta">Last updated: {{ now()->format('F j, Y') }}</div>
    </div>

    @include('frontend.policies._nav')

    <div class="policy-body">
        <h2>1. Information We Collect</h2>
        <p>When you create an account, place an order, or contact us, we may collect:</p>
        <ul>
            <li>Personal identification: name, email address, phone number.</li>
            <li>Delivery address and order preferences.</li>
            <li>Payment information (processed securely by Paystack; we do not store card details).</li>
            <li>Device and browsing data such as IP address, browser type, and pages visited.</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Process and fulfil your orders, including delivery.</li>
            <li>Communicate with you about your order status, returns or support requests.</li>
            <li>Improve our website, products and customer service.</li>
            <li>Send promotional emails only where you have opted in. You may unsubscribe at any time.</li>
            <li>Detect and prevent fraud, abuse and security incidents.</li>
        </ul>

        <h2>3. Sharing Your Information</h2>
        <p>We do not sell or rent your personal data. We may share your information with:</p>
        <ul>
            <li>Logistics partners for the purpose of delivering your order.</li>
            <li>Payment processors (Paystack) to authorise and settle transactions.</li>
            <li>Service providers who help us operate the website under strict confidentiality agreements.</li>
            <li>Law enforcement or regulators where required by law.</li>
        </ul>

        <h2>4. Data Security</h2>
        <p>We implement reasonable technical and organisational measures to protect your personal information, including encryption in transit (HTTPS), access controls, and regular security reviews. However, no method of transmission over the Internet is 100% secure.</p>

        <h2>5. Cookies</h2>
        <p>We use cookies and similar technologies to remember your session, store cart contents, and analyse website traffic. You may disable cookies in your browser settings, but some features of the site may not function properly.</p>

        <h2>6. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
            <li>Access the personal information we hold about you.</li>
            <li>Request correction of inaccurate information.</li>
            <li>Request deletion of your account and associated data.</li>
            <li>Opt out of marketing communications at any time.</li>
        </ul>
        <p>To exercise any of these rights, email <a href="mailto:privacy@arkandstar.com">privacy@arkandstar.com</a>.</p>

        <h2>7. Children's Privacy</h2>
        <p>Our website is not intended for individuals under the age of 18. We do not knowingly collect information from minors. If you believe a child has provided information to us, please contact us immediately.</p>

        <h2>8. Retention of Data</h2>
        <p>We retain your personal information for as long as your account is active or as necessary to provide services, comply with legal obligations, resolve disputes and enforce agreements.</p>

        <h2>9. Changes to This Policy</h2>
        <p>We may update this Privacy Policy periodically. We will notify you of significant changes by posting a notice on the website or sending an email. Continued use after changes constitutes acceptance.</p>

        <h2>10. Contact</h2>
        <p>Questions? Email <a href="mailto:privacy@arkandstar.com">privacy@arkandstar.com</a>.</p>
    </div>
</div>
@endsection