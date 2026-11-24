@extends('frontend.layout')

@push('styles')
<style>
    /* ===== CONTACT US PAGE ===== */

    /* -- Hero -- */
    .contact-hero {
        background: linear-gradient(135deg, var(--ark-green) 0%, var(--ark-green-deep) 100%);
        color: #fff;
        padding: 70px 20px 60px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .contact-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .contact-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -60px;
        width: 240px; height: 240px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .contact-hero h1 {
        font-size: 38px;
        font-weight: 800;
        margin: 0 0 10px;
        letter-spacing: -0.03em;
        position: relative;
        z-index: 1;
    }
    .contact-hero p {
        font-size: 17px;
        opacity: 0.92;
        max-width: 560px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* -- Quick info cards -- */
    .info-cards-section {
        max-width: 1200px;
        margin: -40px auto 0;
        padding: 0 20px;
        position: relative;
        z-index: 2;
    }
    .info-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    @media (max-width: 900px) {
        .info-cards { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 560px) {
        .info-cards { grid-template-columns: 1fr; }
    }
    .info-card {
        background: #fff;
        border-radius: 14px;
        padding: 28px 24px;
        border: 1px solid rgba(13,90,57,0.10);
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        text-align: center;
        transition: transform 0.25s, box-shadow 0.25s;
    }
    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(13,90,57,0.12);
    }
    .info-card .icon-circle {
        width: 56px; height: 56px;
        margin: 0 auto 16px;
        background: #f0fdf4;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .info-card .icon-circle svg {
        width: 26px; height: 26px;
        color: var(--ark-green);
    }
    .info-card h3 {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ark-muted);
        margin: 0 0 8px;
    }
    .info-card .info-value {
        font-size: 15px;
        font-weight: 600;
        color: var(--ark-ink);
        line-height: 1.5;
        word-break: break-word;
    }
    .info-card a {
        color: var(--ark-green);
        text-decoration: none;
        font-weight: 600;
    }
    .info-card a:hover { text-decoration: underline; }

    /* -- Main content layout -- */
    .contact-main {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    .contact-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: start;
    }
    @media (max-width: 900px) {
        .contact-layout { grid-template-columns: 1fr; }
    }

    /* -- Form + Map cards -- */
    .form-card, .map-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid rgba(13,90,57,0.10);
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        padding: 36px;
    }
    .form-card h2, .map-card h2 {
        font-size: 22px;
        font-weight: 800;
        color: var(--ark-ink);
        margin: 0 0 6px;
    }
    .form-card .sub, .map-card .sub {
        font-size: 14px;
        color: var(--ark-muted);
        margin: 0 0 28px;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    @media (max-width: 560px) {
        .form-row { grid-template-columns: 1fr; }
    }
    .form-group { margin-bottom: 18px; }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }
    .form-group label .req { color: #ef4444; }
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 13px 16px;
        border: 1.5px solid #d1d5db;
        border-radius: 10px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        color: var(--ark-ink);
        background: #fff;
        transition: border 0.2s, box-shadow 0.2s;
    }
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--ark-green);
        outline: none;
        box-shadow: 0 0 0 4px rgba(13,90,57,0.1);
    }
    .form-group textarea {
        resize: vertical;
        min-height: 130px;
    }
    .form-group .error-text {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
        font-weight: 600;
    }
    .btn-submit {
        width: 100%;
        background: var(--ark-green);
        color: #fff;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        letter-spacing: 0.02em;
        margin-top: 6px;
    }
    .btn-submit:hover { background: var(--ark-green-deep); }
    .btn-submit:active { transform: scale(0.98); }

    /* -- Map section -- */
    .map-embed {
        width: 100%;
        height: 320px;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-top: 8px;
    }
    .map-address-line {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 20px;
        padding: 16px;
        background: #f9fafb;
        border-radius: 10px;
    }
    .map-address-line svg {
        width: 22px; height: 22px;
        color: var(--ark-green);
        flex-shrink: 0;
        margin-top: 2px;
    }
    .map-address-line .addr-text {
        font-size: 14px;
        color: var(--ark-ink);
        line-height: 1.6;
        font-weight: 500;
    }
    .map-address-line .addr-text strong {
        display: block;
        font-weight: 800;
        color: var(--ark-green);
        margin-bottom: 2px;
    }
    .hours-block {
        margin-top: 16px;
        padding: 16px;
        background: #f0fdf4;
        border-radius: 10px;
        border: 1px solid #bbf7d0;
    }
    .hours-block .hours-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--ark-green);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .hours-block .hours-title svg { width: 16px; height: 16px; }
    .hours-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        padding: 4px 0;
        color: var(--ark-ink);
    }
    .hours-row .day { font-weight: 600; }
    .hours-row .time { color: var(--ark-muted); }
    .hours-row.closed .time { color: #ef4444; font-weight: 700; }

    /* -- Branches section -- */
    .branches-section {
        background: #f9fafb;
        padding: 70px 20px;
        margin-top: 20px;
    }
    .branches-inner { max-width: 1200px; margin: 0 auto; }
    .branches-header {
        text-align: center;
        margin-bottom: 50px;
    }
    .branches-header h2 {
        font-size: 32px;
        font-weight: 800;
        color: var(--ark-ink);
        margin: 0 0 10px;
        letter-spacing: -0.02em;
    }
    .branches-header p {
        font-size: 16px;
        color: var(--ark-muted);
        max-width: 500px;
        margin: 0 auto;
    }
    .branches-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 28px;
    }
    @media (max-width: 1000px) {
        .branches-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .branches-grid { grid-template-columns: 1fr; }
    }
    .branch-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(13,90,57,0.10);
        box-shadow: 0 2px 16px rgba(0,0,0,0.04);
        transition: transform 0.25s, box-shadow 0.25s;
        display: flex;
        flex-direction: column;
    }
    .branch-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(13,90,57,0.10);
    }
    .branch-map {
        width: 100%;
        height: 180px;
        border: none;
        display: block;
    }
    .branch-body {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .branch-badge {
        display: inline-block;
        background: #f0fdf4;
        color: var(--ark-green);
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 12px;
        align-self: flex-start;
    }
    .branch-body h3 {
        font-size: 20px;
        font-weight: 800;
        color: var(--ark-ink);
        margin: 0 0 14px;
    }
    .branch-detail {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 10px;
        font-size: 14px;
        color: var(--ark-ink);
        line-height: 1.5;
    }
    .branch-detail svg {
        width: 18px; height: 18px;
        color: var(--ark-green);
        flex-shrink: 0;
        margin-top: 2px;
    }
    .branch-detail a {
        color: var(--ark-green);
        text-decoration: none;
        font-weight: 600;
    }
    .branch-detail a:hover { text-decoration: underline; }
    .branch-actions {
        margin-top: auto;
        padding-top: 16px;
    }
    .btn-directions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 11px;
        background: var(--ark-green);
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        font-size: 14px;
        transition: background 0.2s;
    }
    .btn-directions:hover { background: var(--ark-green-deep); }
    .btn-directions svg { width: 18px; height: 18px; }

    /* -- Alerts -- */
    .alert-success, .alert-error {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 24px;
        font-weight: 600;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .alert-success {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .alert-error {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .alert-success svg, .alert-error svg { width: 20px; height: 20px; flex-shrink: 0; }

    /* -- CTA banner -- */
    .cta-banner {
        background: linear-gradient(135deg, var(--ark-green) 0%, var(--ark-green-deep) 100%);
        padding: 50px 20px;
        text-align: center;
    }
    .cta-banner h3 {
        font-size: 24px;
        font-weight: 800;
        color: #fff;
        margin: 0 0 8px;
    }
    .cta-banner p {
        font-size: 15px;
        color: rgba(255,255,255,0.9);
        margin: 0 0 20px;
    }
    .cta-btn {
        display: inline-block;
        padding: 14px 36px;
        background: #fff;
        color: var(--ark-green);
        border-radius: 10px;
        text-decoration: none;
        font-weight: 800;
        font-size: 15px;
        transition: transform 0.15s;
    }
    .cta-btn:hover { transform: scale(1.03); }
</style>
@endpush

@section('content')

@php
    // ── Base settings (general setting fallbacks) ────────────────
    $gs = $generalSetting ?? null;
    $companyName    = $gs ? ($gs->company_name ?? config('app.name', 'JoexPOS')) : config('app.name', 'JoexPOS');
    $companyPhone   = $gs ? ($gs->phone ?? $gs->phone_number ?? null) : null;
    $companyEmail   = $gs ? ($gs->email ?? null) : null;
    $companyAddress = $gs ? ($gs->address ?? $gs->company_address ?? null) : null;
    $companyCity    = $gs ? ($gs->city ?? null) : null;
    $openTime       = $gs ? ($gs->opening_time ?? '8:00 AM') : '8:00 AM';
    $closeTime      = $gs ? ($gs->closing_time ?? '6:00 PM') : '6:00 PM';
    $mapHelper      = \App\Http\Controllers\ContactController::class;

    // ── ecommerce_setup overrides (single source of truth) ────────
    $setupPhone   = setting('contact.phone');
    $setupEmail   = setting('contact.email');
    $setupAddress = setting('address.line1');
    $setupCity    = setting('address.city');
    $setupState   = setting('address.state');
    $setupCountry = setting('address.country');

    $companyPhone   = $setupPhone   ?: $companyPhone;
    $companyEmail   = $setupEmail   ?: $companyEmail;
    $companyAddress = $setupAddress ?: $companyAddress;
    $companyCity    = $setupCity    ?: $companyCity;

    // Full display address
    $fullAddress = trim(collect([$companyAddress, $companyCity, $setupState, $setupCountry])->filter()->implode(', '));
    if ($fullAddress === '') {
        $fullAddress = $companyAddress ?: $companyCity;
    }

    // ── Contact page content ──────────────────────────────────────
    $cp = setting('contact_page', []);

    $cpHero     = $cp['hero']       ?? [];
    $cpInfo     = $cp['info_cards'] ?? [];
    $cpForm     = $cp['form']       ?? [];
    $cpMap      = $cp['map']        ?? [];
    $cpHours    = $cp['hours']      ?? [];
    $cpBranches = $cp['branches']   ?? [];
    $cpCta      = $cp['cta']        ?? [];

    $heroHeading = $cpHero['heading']     ?? 'Get in Touch';
    $heroDesc    = $cpHero['description'] ?? "We'd love to hear from you. Whether you have a question about a product, need help with an order, or just want to say hello - our team is here to help.";

    $visitLabel = $cpInfo['visit_label'] ?? 'Visit Us';
    $callLabel  = $cpInfo['call_label']  ?? 'Call Us';
    $emailLabel = $cpInfo['email_label'] ?? 'Email Us';
    $hoursLabel = $cpInfo['hours_label'] ?? 'Open Hours';

    $formHeading    = $cpForm['heading']     ?? 'Send Us a Message';
    $formSub        = $cpForm['sub']         ?? "Fill out the form below and we'll get back to you as soon as possible.";
    $formSubmitText = $cpForm['submit_text'] ?? 'Send Message';

    $mapHeading  = $cpMap['heading']   ?? 'Find Us on the Map';
    $mapSub      = $cpMap['sub']       ?? 'Visit our main office or get in touch using the details below.';
    $mapEmbedUrl = $cpMap['embed_url'] ?? null;

    // Hours labels
    $weekdaysLabel  = $cpHours['weekdays_label']   ?? 'Monday - Friday';
    $saturdayLabel  = $cpHours['saturday_label']   ?? 'Saturday';
    $sundayLabel    = $cpHours['sunday_label']     ?? 'Sunday';
    $saturdayClose  = $cpHours['saturday_close']   ?? '2:00 PM';
    $infoHoursTop   = $cpHours['info_top_label']   ?? 'Mon - Saturday';
    $infoSundayNote = $cpHours['info_sunday_note'] ?? 'Sunday: Closed';

    $branchesHeading = $cpBranches['heading']         ?? 'Our Branches';
    $branchesSub     = $cpBranches['sub']             ?? 'Find a branch near you. Each location is fully stocked and ready to serve you.';
    $branchesBadge   = $cpBranches['badge_text']      ?? 'Branch';
    $directionsText  = $cpBranches['directions_text'] ?? 'Get Directions';

    $ctaHeading     = $cpCta['heading']     ?? 'Ready to Start Shopping?';
    $ctaDescription = $cpCta['description'] ?? 'Browse our full catalogue and place your order online - pay with Paystack or pay on delivery.';
    $ctaButtonText  = $cpCta['button_text'] ?? 'Shop Now';
    $ctaButtonLink  = $cpCta['button_link'] ?? (\Route::has('shop.index') ? route('shop.index') : '#');

    // Resolve map URL — DB override wins, else fall back to auto-generated
    $autoMapUrl = (isset($mainAddress) && $mainAddress) ? $mapHelper::mapEmbedUrl($mainAddress) : null;
    $resolvedMapUrl = $mapEmbedUrl ?: $autoMapUrl;
@endphp

<!-- HERO SECTION -->
<section class="contact-hero">
    <h1>{{ $heroHeading }}</h1>
    <p>{{ $heroDesc }}</p>
</section>

<!-- QUICK INFO CARDS -->
<section class="info-cards-section">
    <div class="info-cards">
        <div class="info-card">
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <h3>{{ $visitLabel }}</h3>
            <div class="info-value">
                {{ $fullAddress ?: $companyName }}
            </div>
        </div>

        <div class="info-card">
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            </div>
            <h3>{{ $callLabel }}</h3>
            <div class="info-value">
                @if($companyPhone)
                    <a href="tel:{{ $companyPhone }}">{{ $companyPhone }}</a>
                @else
                    Not available
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3>{{ $emailLabel }}</h3>
            <div class="info-value">
                @if($companyEmail)
                    <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
                @else
                    Not available
                @endif
            </div>
        </div>

        <div class="info-card">
            <div class="icon-circle">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3>{{ $hoursLabel }}</h3>
            <div class="info-value">
                {{ $infoHoursTop }}<br>
                {{ $openTime }} - {{ $closeTime }}<br>
                <span style="color:#ef4444;font-size:12px;">{{ $infoSundayNote }}</span>
            </div>
        </div>
    </div>
</section>

<!-- FORM + MAP -->
<section class="contact-main">
    <div class="contact-layout">

        <!-- LEFT: Contact Form -->
        <div class="form-card">
            <h2>{{ $formHeading }}</h2>
            <p class="sub">{{ $formSub }}</p>

            @if(session('success'))
                <div class="alert-success">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.67 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-error">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.67 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Please correct the errors below and try again.
                </div>
            @endif

            <form action="{{ route('contact.us.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name <span class="req">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                        @error('name') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+233 24 000 0000">
                        @error('phone') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Address <span class="req">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                        @error('email') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Subject <span class="req">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="How can we help?" required>
                        @error('subject') <div class="error-text">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Message <span class="req">*</span></label>
                    <textarea name="message" placeholder="Tell us more about your inquiry..." required>{{ old('message') }}</textarea>
                    @error('message') <div class="error-text">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-submit">{{ $formSubmitText }}</button>
            </form>
        </div>

        <!-- RIGHT: Map + Address + Hours -->
        <div class="map-card">
            <h2>{{ $mapHeading }}</h2>
            <p class="sub">{{ $mapSub }}</p>

            @if($resolvedMapUrl || $fullAddress)
                @if($resolvedMapUrl)
                    <iframe
                        class="map-embed"
                        src="{{ $resolvedMapUrl }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen>
                    </iframe>
                @endif

                <div class="map-address-line">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <div class="addr-text">
                        <strong>{{ $companyName }}</strong>
                        {{ $fullAddress }}
                        @if($companyPhone)
                            <br><a href="tel:{{ $companyPhone }}" style="color:var(--ark-green);font-weight:600;">{{ $companyPhone }}</a>
                        @endif
                        @if($companyEmail)
                            <br><a href="mailto:{{ $companyEmail }}" style="color:var(--ark-green);font-weight:600;">{{ $companyEmail }}</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="map-address-line">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="addr-text">Address not configured in settings yet.</div>
                </div>
            @endif

            <div class="hours-block">
                <div class="hours-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Business Hours
                </div>
                <div class="hours-row"><span class="day">{{ $weekdaysLabel }}</span><span class="time">{{ $openTime }} - {{ $closeTime }}</span></div>
                <div class="hours-row"><span class="day">{{ $saturdayLabel }}</span><span class="time">{{ $openTime }} - {{ $saturdayClose }}</span></div>
                <div class="hours-row closed"><span class="day">{{ $sundayLabel }}</span><span class="time">Closed</span></div>
            </div>
        </div>
    </div>
</section>

<!-- BRANCHES -->
@if($branches->isNotEmpty())
<section class="branches-section">
    <div class="branches-inner">
        <div class="branches-header">
            <h2>{{ $branchesHeading }}</h2>
            <p>{{ $branchesSub }}</p>
        </div>

        <div class="branches-grid">
            @foreach($branches as $branch)
                @php
                    $branchAddress = $mapHelper::buildBranchAddress($branch);
                    $branchPhone   = $branch->phone ?? $branch->phone_number ?? null;
                    $branchEmail   = $branch->email ?? null;
                @endphp
                <div class="branch-card">
                    @if($branchAddress)
                        <iframe
                            class="branch-map"
                            src="{{ $mapHelper::mapEmbedUrl($branchAddress) }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    @endif

                    <div class="branch-body">
                        <span class="branch-badge">{{ $branchesBadge }}</span>
                        <h3>{{ $branch->name }}</h3>

                        @if($branchAddress)
                            <div class="branch-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>{{ $branchAddress }}</span>
                            </div>
                        @endif

                        @if($branchPhone)
                            <div class="branch-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <a href="tel:{{ $branchPhone }}">{{ $branchPhone }}</a>
                            </div>
                        @endif

                        @if($branchEmail)
                            <div class="branch-detail">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <a href="mailto:{{ $branchEmail }}">{{ $branchEmail }}</a>
                            </div>
                        @endif

                        <div class="branch-detail">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ $infoHoursTop }}: {{ $openTime }} - {{ $closeTime }}</span>
                        </div>

                        <div class="branch-actions">
                            @if($branchAddress)
                                <a href="{{ $mapHelper::mapDirectionsUrl($branchAddress) }}"
                                   target="_blank" rel="noopener" class="btn-directions">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 3V4m0 0L9 7"></path></svg>
                                    {{ $directionsText }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- CTA BANNER -->
<section class="cta-banner">
    <h3>{{ $ctaHeading }}</h3>
    <p>{{ $ctaDescription }}</p>
    <a href="{{ $ctaButtonLink }}" class="cta-btn">{{ $ctaButtonText }}</a>
</section>

@endsection
