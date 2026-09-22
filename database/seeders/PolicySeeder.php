<?php

namespace Database\Seeders;

use App\Models\Policy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'title'       => 'Terms of Service',
                'slug'        => 'terms',
                'sort_order' => 1,
                'body'        => $this->termsBody(),
            ],
            [
                'title'       => 'Privacy Policy',
                'slug'        => 'privacy',
                'sort_order' => 2,
                'body'        => $this->privacyBody(),
            ],
            [
                'title'       => 'Return & Refund Policy',
                'slug'        => 'returns',
                'sort_order' => 3,
                'body'        => $this->returnsBody(),
            ],
            [
                'title'       => 'Shipping Policy',
                'slug'        => 'shipping',
                'sort_order' => 4,
                'body'        => $this->shippingBody(),
            ],
            [
                'title'       => 'Cancellation Policy',
                'slug'        => 'cancellation',
                'sort_order' => 5,
                'body'        => $this->cancellationBody(),
            ],
            [
                'title'       => 'Warranty Policy',
                'slug'        => 'warranty',
                'sort_order' => 6,
                'body'        => $this->warrantyBody(),
            ],
        ];

        foreach ($policies as $policy) {
            Policy::updateOrCreate(
                ['slug' => $policy['slug']],
                array_merge($policy, [
                    'is_active' => true,
                    'updated_at' => Carbon::now(),
                ])
            );
        }
    }

    private function termsBody(): string
    {
        return <<<HTML
<h2>1. Introduction</h2>
<p>Welcome to Ark &amp; Star Enterprise ("we", "us", or "our"). By accessing or using our website arkandstar.com and placing an order, you agree to be bound by these Terms of Service. If you do not agree, please discontinue use of the site.</p>

<h2>2. Account Registration</h2>
<p>To place orders you must create a customer account. You agree to provide accurate, complete and current information at registration and to keep it updated. You are responsible for safeguarding your password and for all activity under your account.</p>

<h2>3. Orders &amp; Pricing</h2>
<p>All orders are subject to availability and confirmation of the order price. We reserve the right to refuse or cancel any order placed for any reason, including pricing errors, and to limit quantities purchased per customer.</p>

<h2>4. Payment</h2>
<p>We accept payments via Paystack (debit/credit card, bank transfer) and Pay-on-Delivery within selected locations. Payment must be received in full before goods are dispatched for prepaid orders.</p>

<h2>5. Shipping &amp; Delivery</h2>
<p>Delivery is handled by our partnered logistics providers. Estimated delivery timeframes are stated at checkout and are not guaranteed. Risk of loss passes to you upon delivery to the address provided.</p>

<h2>6. Returns &amp; Refunds</h2>
<p>Eligible items may be returned within 7 days of delivery subject to the conditions stated in our Return &amp; Refund Policy.</p>

<h2>7. Product Information</h2>
<p>We make every effort to display product images and descriptions accurately. However, slight variations may occur between the displayed image and the physical product.</p>

<h2>8. Intellectual Property</h2>
<p>All content on this website is the property of Ark &amp; Star Enterprise and is protected by applicable intellectual property laws.</p>

<h2>9. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, our total liability shall not exceed the amount paid by you for the specific product giving rise to the claim.</p>

<h2>10. Modification of Terms</h2>
<p>We reserve the right to update these Terms at any time. Continued use of the website after changes constitutes acceptance of the revised Terms.</p>

<h2>11. Governing Law</h2>
<p>These Terms are governed by the laws of the Federal Republic of Nigeria.</p>

<h2>12. Contact</h2>
<p>For questions regarding these Terms, contact us at <a href="mailto:support@arkandstar.com">support@arkandstar.com</a>.</p>
HTML;
    }

    private function privacyBody(): string
    {
        return <<<HTML
<h2>1. Information We Collect</h2>
<p>When you create an account, place an order, or contact us, we may collect personal identification, delivery address, payment information (processed securely by Paystack), and device and browsing data.</p>

<h2>2. How We Use Your Information</h2>
<p>We use your information to process and fulfil orders, communicate about order status, improve our services, send promotional emails (where opted in), and detect fraud.</p>

<h2>3. Sharing Your Information</h2>
<p>We do not sell or rent your personal data. We may share information with logistics partners, payment processors, service providers, and law enforcement where required.</p>

<h2>4. Data Security</h2>
<p>We implement reasonable technical and organisational measures including encryption in transit, access controls, and security reviews.</p>

<h2>5. Cookies</h2>
<p>We use cookies to remember your session, store cart contents, and analyse website traffic. You may disable cookies in your browser settings.</p>

<h2>6. Your Rights</h2>
<p>You may access, correct, delete, or opt out of marketing communications at any time. Email <a href="mailto:privacy@arkandstar.com">privacy@arkandstar.com</a>.</p>

<h2>7. Children's Privacy</h2>
<p>Our website is not intended for individuals under 18 years of age.</p>

<h2>8. Changes to This Policy</h2>
<p>We may update this Privacy Policy periodically. Continued use after changes constitutes acceptance.</p>

<h2>9. Contact</h2>
<p>Questions? Email <a href="mailto:privacy@arkandstar.com">privacy@arkandstar.com</a>.</p>
HTML;
    }

    private function returnsBody(): string
    {
        return <<<HTML
<p>Eligible items may be returned within <strong>7 days</strong> of delivery for a full refund, provided they are unused, in original packaging, and accompanied by proof of purchase.</p>

<h2>1. Return Eligibility</h2>
<p>Items must be unused, in original packaging with tags, manuals and accessories intact, and accompanied by the original receipt or order reference.</p>

<h2>2. Non-Returnable Items</h2>
<p>Perishable goods, personal care items, customised products, gift cards, downloadable software, and items marked "Final Sale" cannot be returned.</p>

<h2>3. How to Initiate a Return</h2>
<p>Email <a href="mailto:returns@arkandstar.com">returns@arkandstar.com</a> with your order reference and reason. Once approved, we will provide return instructions.</p>

<h2>4. Refund Processing</h2>
<p>Refunds to the original payment method take 5–10 business days. Pay-on-Delivery orders will be refunded via bank transfer.</p>

<h2>5. Damaged or Defective Items</h2>
<p>Contact us within 48 hours of delivery with photo evidence for a free replacement or full refund.</p>

<h2>6. Shipping Costs</h2>
<p>Return shipping costs are the customer's responsibility unless the return is due to our error.</p>
HTML;
    }

    private function shippingBody(): string
    {
        return <<<HTML
<h2>1. Order Processing Time</h2>
<p>Orders are processed within 1–2 business days (Monday–Friday, excluding public holidays).</p>

<h2>2. Delivery Areas</h2>
<p>We currently deliver to all states in the Federal Republic of Nigeria.</p>

<h2>3. Shipping Methods &amp; Timeframes</h2>
<table class="policy-table">
    <thead><tr><th>Method</th><th>Estimated Delivery</th><th>Service Areas</th></tr></thead>
    <tbody>
        <tr><td>Standard</td><td>3–5 business days</td><td>Nationwide</td></tr>
        <tr><td>Express</td><td>1–2 business days</td><td>Lagos &amp; Abuja</td></tr>
        <tr><td>Same-Day</td><td>Within 6 hours</td><td>Select Lagos areas</td></tr>
    </tbody>
</table>

<h2>4. Shipping Costs</h2>
<p>Calculated at checkout based on location, weight, and method. Free standard shipping applies to orders above ₦50,000 within Lagos.</p>

<h2>5. Order Tracking</h2>
<p>You will receive an email and SMS with a tracking number once your order has been dispatched.</p>

<h2>6. Failed Delivery</h2>
<p>Our courier will attempt delivery up to two times. A re-delivery fee may apply.</p>

<h2>7. Incorrect Address</h2>
<p>Re-shipping charges will apply if the package is returned due to an incorrect address supplied by the customer.</p>
HTML;
    }

    private function cancellationBody(): string
    {
        return <<<HTML
<h2>1. Customer-Initiated Cancellations</h2>
<p>You may cancel an order at no charge provided the order has not yet been dispatched. Contact <a href="mailto:support@arkandstar.com">support@arkandstar.com</a> immediately.</p>

<h2>2. Cancellation Timeframe</h2>
<p>Requests must be received within 2 hours for Same-Day/Express and 12 hours for Standard delivery options.</p>

<h2>3. Order Modification</h2>
<p>Minor modifications (address, phone) can be made before dispatch. Changes to product or quantity require cancelling and reordering.</p>

<h2>4. Seller-Initiated Cancellations</h2>
<p>We reserve the right to cancel orders for out-of-stock items, pricing errors, suspected fraud, quantity limits, or delivery constraints. A full refund will be issued.</p>

<h2>5. Cancellation After Dispatch</h2>
<p>Once dispatched, cancellation is not possible. You may refuse delivery or initiate a return once received.</p>
HTML;
    }

    private function warrantyBody(): string
    {
        return <<<HTML
<h2>1. Warranty Coverage</h2>
<p>Warranty coverage applies only to products explicitly marked as "Warranty Included" on the product page and is valid for the original purchaser only.</p>

<h2>2. Warranty Period</h2>
<ul>
    <li>Electronics &amp; appliances: 12 months.</li>
    <li>Furniture: 6 months.</li>
    <li>Other warranted items: 3 months (unless stated otherwise).</li>
</ul>

<h2>3. What is Covered</h2>
<p>Manufacturing defects in materials or workmanship and product failure under normal, intended use.</p>

<h2>4. What is Not Covered</h2>
<p>Misuse, abuse, normal wear, unauthorised repairs, power surge damage, and consumable parts.</p>

<h2>5. How to Make a Warranty Claim</h2>
<p>Email <a href="mailto:warranty@arkandstar.com">warranty@arkandstar.com</a> with your order reference, a description of the defect, photos/video, and the product serial number if applicable.</p>

<h2>6. Remedies</h2>
<p>For approved claims, we will repair, replace, or refund at our discretion.</p>

<h2>7. Limitations</h2>
<p>Our liability under this warranty is limited to the purchase price of the product.</p>
HTML;
    }
}