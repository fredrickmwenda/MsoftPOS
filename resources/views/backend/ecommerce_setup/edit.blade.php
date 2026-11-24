@extends('backend.layout.main')

@section('content')

@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    {{ session()->get('not_permitted') }}
  </div>
@endif

@if(session('message'))
  <div class="alert alert-success alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    {{ session('message') }}
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php
    $id    = $settings['identity']     ?? [];
    $ct    = $settings['contact']      ?? [];
    $ad    = $settings['address']      ?? [];
    $hr    = $settings['hours']        ?? [];
    $cm    = $settings['commerce']     ?? [];
    $hp    = $settings['homepage']     ?? [];
    $hpHero= $hp['hero']       ?? [];
    $hpCat = $hp['categories'] ?? [];
    $hpDeal= $hp['deal']       ?? [];
    $hpFeat= $hp['featured']   ?? [];
    $cp    = $settings['contact_page'] ?? [];
    $cpHero= $cp['hero']       ?? [];
    $cpInfo= $cp['info_cards'] ?? [];
    $cpForm= $cp['form']       ?? [];
    $cpMap = $cp['map']        ?? [];
    $cpHrs = $cp['hours']      ?? [];
    $cpBr  = $cp['branches']   ?? [];
    $cpCta = $cp['cta']        ?? [];
@endphp

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">E-commerce Setup</h4>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="expand-all">Expand All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="collapse-all">Collapse All</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>Edit storefront content — changes appear on the frontend after saving. Fields marked <strong>*</strong> are required.</small></p>

                        {!! Form::open(['route' => 'ecommerce.setup.update', 'method' => 'post', 'files' => true]) !!}

                        {{-- ================= IDENTITY ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-identity" style="cursor:pointer">
                                <h5 class="mb-0">🏢 Identity</h5>
                            </div>
                            <div id="sec-identity" class="collapse show">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Store / Company Name</strong></label>
                                                <input type="text" name="identity[name]" class="form-control"
                                                       value="{{ old('identity.name', $id['name'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Logo Filename</strong> <small class="text-muted">(inside /public/logo)</small></label>
                                                <input type="text" name="identity[logo]" class="form-control"
                                                       value="{{ old('identity.logo', $id['logo'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Upload New Logo</strong></label>
                                                <input type="file" name="logo_file" class="form-control-file" accept="image/*">
                                                @if(!empty($id['logo']))
                                                    <small class="text-muted d-block mt-1">Current: <code>{{ $id['logo'] }}</code></small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Short Description</strong></label>
                                                <textarea name="identity[description]" rows="2" class="form-control">{{ old('identity.description', $id['description'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-contact" style="cursor:pointer">
                                <h5 class="mb-0">📞 Contact Details</h5>
                            </div>
                            <div id="sec-contact" class="collapse show">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Primary Email</strong></label>
                                                <input type="email" name="contact[email]" class="form-control"
                                                       value="{{ old('contact.email', $ct['email'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Secondary Email</strong></label>
                                                <input type="email" name="contact[email_two]" class="form-control"
                                                       value="{{ old('contact.email_two', $ct['email_two'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Support Email</strong></label>
                                                <input type="email" name="contact[support_email]" class="form-control"
                                                       value="{{ old('contact.support_email', $ct['support_email'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Phone *</strong></label>
                                                <input type="text" name="contact[phone]" class="form-control"
                                                       value="{{ old('contact.phone', $ct['phone'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>WhatsApp</strong></label>
                                                <input type="text" name="contact[whatsapp]" class="form-control"
                                                       value="{{ old('contact.whatsapp', $ct['whatsapp'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= ADDRESS ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-address" style="cursor:pointer">
                                <h5 class="mb-0">📍 Address / Locations</h5>
                            </div>
                            <div id="sec-address" class="collapse show">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Primary Location</strong></label>
                                                <input type="text" name="address[location]" class="form-control"
                                                       value="{{ old('address.location', $ad['location'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Secondary Location</strong></label>
                                                <input type="text" name="address[location_two]" class="form-control"
                                                       value="{{ old('address.location_two', $ad['location_two'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= BUSINESS HOURS ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-hours" style="cursor:pointer">
                                <h5 class="mb-0">🕒 Business Hours</h5>
                            </div>
                            <div id="sec-hours" class="collapse show">
                                <div class="card-body">
                                    <div id="hours-repeater">
                                        @php
                                            $hoursRows = old('hours_keys')
                                                ? collect(old('hours_keys'))->map(fn($k, $i) => ['key' => $k, 'value' => old('hours_values')[$i] ?? ''])
                                                : collect($hr)->map(fn($v, $k) => ['key' => $k, 'value' => $v]);
                                            if ($hoursRows->isEmpty()) $hoursRows = collect([['key' => '', 'value' => '']]);
                                        @endphp
                                        @foreach($hoursRows as $i => $row)
                                            <div class="form-row mb-2 hours-row">
                                                <div class="col-md-4">
                                                    <input type="text" name="hours_keys[]" class="form-control"
                                                           placeholder="e.g. Weekday" value="{{ $row['key'] }}">
                                                </div>
                                                <div class="col-md-7">
                                                    <input type="text" name="hours_values[]" class="form-control"
                                                           placeholder="e.g. 8a.m – 10p.m" value="{{ $row['value'] }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-sm btn-danger remove-hours">×</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-hours">+ Add row</button>
                                </div>
                            </div>
                        </div>

                        {{-- ================= COMMERCE ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-commerce" style="cursor:pointer">
                                <h5 class="mb-0">🛒 Commerce Defaults</h5>
                            </div>
                            <div id="sec-commerce" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Currency Code</strong></label>
                                                <input type="text" name="commerce[currency]" class="form-control"
                                                       placeholder="GHS" value="{{ old('commerce.currency', $cm['currency'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Free Shipping Over</strong></label>
                                                <input type="number" step="0.01" name="commerce[free_shipping_over]" class="form-control"
                                                       value="{{ old('commerce.free_shipping_over', $cm['free_shipping_over'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Announcement Bar Text</strong></label>
                                                <input type="text" name="commerce[announcement]" class="form-control"
                                                       value="{{ old('commerce.announcement', $cm['announcement'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= HOMEPAGE HERO ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-hp-hero" style="cursor:pointer">
                                <h5 class="mb-0">🏠 Homepage — Hero</h5>
                            </div>
                            <div id="sec-hp-hero" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Badge</strong> <small class="text-muted">(blank = uses site title)</small></label>
                                                <input type="text" name="homepage[hero][badge]" class="form-control"
                                                       value="{{ old('homepage.hero.badge', $hpHero['badge'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Title — prefix</strong></label>
                                                <input type="text" name="homepage[hero][title_prefix]" class="form-control"
                                                       value="{{ old('homepage.hero.title_prefix', $hpHero['title_prefix'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Title — highlighted</strong></label>
                                                <input type="text" name="homepage[hero][title_highlight]" class="form-control"
                                                       value="{{ old('homepage.hero.title_highlight', $hpHero['title_highlight'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <textarea name="homepage[hero][description]" rows="2" class="form-control">{{ old('homepage.hero.description', $hpHero['description'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Primary Button Text</strong></label>
                                                <input type="text" name="homepage[hero][primary_btn_text]" class="form-control"
                                                       value="{{ old('homepage.hero.primary_btn_text', $hpHero['primary_btn_text'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Primary Button Link</strong></label>
                                                <input type="text" name="homepage[hero][primary_btn_link]" class="form-control"
                                                       value="{{ old('homepage.hero.primary_btn_link', $hpHero['primary_btn_link'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Secondary Button Text</strong></label>
                                                <input type="text" name="homepage[hero][secondary_btn_text]" class="form-control"
                                                       value="{{ old('homepage.hero.secondary_btn_text', $hpHero['secondary_btn_text'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><strong>Secondary Button Link</strong></label>
                                                <input type="text" name="homepage[hero][secondary_btn_link]" class="form-control"
                                                       value="{{ old('homepage.hero.secondary_btn_link', $hpHero['secondary_btn_link'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Warranty Card — Title</strong></label>
                                                <input type="text" name="homepage[hero][warranty_title]" class="form-control"
                                                       value="{{ old('homepage.hero.warranty_title', $hpHero['warranty_title'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Warranty Card — Subtitle</strong></label>
                                                <input type="text" name="homepage[hero][warranty_subtitle]" class="form-control"
                                                       value="{{ old('homepage.hero.warranty_subtitle', $hpHero['warranty_subtitle'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="mb-2"><strong>Hero Stats</strong> <small class="text-muted">(value + label)</small></h6>
                                    <div id="stats-repeater">
                                        @php
                                            $statsRows = old('homepage.hero.stats', $hpHero['stats'] ?? [['value' => '', 'label' => '']]);
                                            if (empty($statsRows)) $statsRows = [['value' => '', 'label' => '']];
                                        @endphp
                                        @foreach($statsRows as $i => $stat)
                                            <div class="form-row mb-2 stat-row">
                                                <div class="col-md-3">
                                                    <input type="text" name="homepage[hero][stats][{{ $i }}][value]" class="form-control"
                                                           placeholder="5,000+" value="{{ $stat['value'] ?? '' }}">
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" name="homepage[hero][stats][{{ $i }}][label]" class="form-control"
                                                           placeholder="Quality products" value="{{ $stat['label'] ?? '' }}">
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-sm btn-danger remove-stat">×</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-stat">+ Add stat</button>
                                </div>
                            </div>
                        </div>

                        {{-- ================= HOMEPAGE CATEGORIES ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-hp-cat" style="cursor:pointer">
                                <h5 class="mb-0">🏠 Homepage — Categories Section</h5>
                            </div>
                            <div id="sec-hp-cat" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Eyebrow</strong></label>
                                                <input type="text" name="homepage[categories][eyebrow]" class="form-control"
                                                       value="{{ old('homepage.categories.eyebrow', $hpCat['eyebrow'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="homepage[categories][heading]" class="form-control"
                                                       value="{{ old('homepage.categories.heading', $hpCat['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>View-all Link Text</strong></label>
                                                <input type="text" name="homepage[categories][view_all_text]" class="form-control"
                                                       value="{{ old('homepage.categories.view_all_text', $hpCat['view_all_text'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= HOMEPAGE DEAL ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-hp-deal" style="cursor:pointer">
                                <h5 class="mb-0">🏠 Homepage — Deal Banner</h5>
                            </div>
                            <div id="sec-hp-deal" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Label</strong></label>
                                                <input type="text" name="homepage[deal][label]" class="form-control"
                                                       value="{{ old('homepage.deal.label', $hpDeal['label'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="homepage[deal][heading]" class="form-control"
                                                       value="{{ old('homepage.deal.heading', $hpDeal['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <input type="text" name="homepage[deal][description]" class="form-control"
                                                       value="{{ old('homepage.deal.description', $hpDeal['description'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Link Text</strong></label>
                                                <input type="text" name="homepage[deal][link_text]" class="form-control"
                                                       value="{{ old('homepage.deal.link_text', $hpDeal['link_text'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Link URL</strong></label>
                                                <input type="text" name="homepage[deal][link_url]" class="form-control"
                                                       value="{{ old('homepage.deal.link_url', $hpDeal['link_url'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Badge — top</strong></label>
                                                <input type="text" name="homepage[deal][badge_top]" class="form-control"
                                                       value="{{ old('homepage.deal.badge_top', $hpDeal['badge_top'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Badge — value</strong></label>
                                                <input type="text" name="homepage[deal][badge_value]" class="form-control"
                                                       value="{{ old('homepage.deal.badge_value', $hpDeal['badge_value'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Badge — bottom</strong></label>
                                                <input type="text" name="homepage[deal][badge_bottom]" class="form-control"
                                                       value="{{ old('homepage.deal.badge_bottom', $hpDeal['badge_bottom'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= HOMEPAGE FEATURED ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-hp-feat" style="cursor:pointer">
                                <h5 class="mb-0">🏠 Homepage — Featured Section</h5>
                            </div>
                            <div id="sec-hp-feat" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Eyebrow</strong></label>
                                                <input type="text" name="homepage[featured][eyebrow]" class="form-control"
                                                       value="{{ old('homepage.featured.eyebrow', $hpFeat['eyebrow'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="homepage[featured][heading]" class="form-control"
                                                       value="{{ old('homepage.featured.heading', $hpFeat['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE HERO ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-hero" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Hero</h5>
                            </div>
                            <div id="sec-cp-hero" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="contact_page[hero][heading]" class="form-control"
                                                       value="{{ old('contact_page.hero.heading', $cpHero['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <textarea name="contact_page[hero][description]" rows="2" class="form-control">{{ old('contact_page.hero.description', $cpHero['description'] ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE INFO CARDS ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-info" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Info Cards</h5>
                            </div>
                            <div id="sec-cp-info" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        @foreach(['visit_label' => 'Visit Us', 'call_label' => 'Call Us', 'email_label' => 'Email Us', 'hours_label' => 'Open Hours'] as $k => $lbl)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label><strong>{{ $lbl }}</strong></label>
                                                    <input type="text" name="contact_page[info_cards][{{ $k }}]" class="form-control"
                                                           value="{{ old("contact_page.info_cards.$k", $cpInfo[$k] ?? '') }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE FORM ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-form" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Form Card</h5>
                            </div>
                            <div id="sec-cp-form" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="contact_page[form][heading]" class="form-control"
                                                       value="{{ old('contact_page.form.heading', $cpForm['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><strong>Sub-text</strong></label>
                                                <input type="text" name="contact_page[form][sub]" class="form-control"
                                                       value="{{ old('contact_page.form.sub', $cpForm['sub'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label><strong>Button Text</strong></label>
                                                <input type="text" name="contact_page[form][submit_text]" class="form-control"
                                                       value="{{ old('contact_page.form.submit_text', $cpForm['submit_text'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE MAP ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-map" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Map Card</h5>
                            </div>
                            <div id="sec-cp-map" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="contact_page[map][heading]" class="form-control"
                                                       value="{{ old('contact_page.map.heading', $cpMap['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Sub-text</strong></label>
                                                <input type="text" name="contact_page[map][sub]" class="form-control"
                                                       value="{{ old('contact_page.map.sub', $cpMap['sub'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label><strong>Google Maps Embed URL</strong> <small class="text-muted">(optional — overrides auto-generated)</small></label>
                                                <input type="text" name="contact_page[map][embed_url]" class="form-control"
                                                       value="{{ old('contact_page.map.embed_url', $cpMap['embed_url'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE HOURS ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-hrs" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Business Hours Labels</h5>
                            </div>
                            <div id="sec-cp-hrs" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        @foreach([
                                            'weekdays_label'   => 'Mon–Fri label',
                                            'saturday_label'   => 'Saturday label',
                                            'sunday_label'     => 'Sunday label',
                                            'saturday_close'   => 'Saturday closing time',
                                            'info_top_label'   => 'Info-card top line',
                                            'info_sunday_note' => 'Info-card Sunday note',
                                        ] as $k => $lbl)
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label><strong>{{ $lbl }}</strong></label>
                                                    <input type="text" name="contact_page[hours][{{ $k }}]" class="form-control"
                                                           value="{{ old("contact_page.hours.$k", $cpHrs[$k] ?? '') }}">
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE BRANCHES ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-br" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — Branches Section</h5>
                            </div>
                            <div id="sec-cp-br" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="contact_page[branches][heading]" class="form-control"
                                                       value="{{ old('contact_page.branches.heading', $cpBr['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Sub-text</strong></label>
                                                <input type="text" name="contact_page[branches][sub]" class="form-control"
                                                       value="{{ old('contact_page.branches.sub', $cpBr['sub'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Badge Text</strong></label>
                                                <input type="text" name="contact_page[branches][badge_text]" class="form-control"
                                                       value="{{ old('contact_page.branches.badge_text', $cpBr['badge_text'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Directions Button Text</strong></label>
                                                <input type="text" name="contact_page[branches][directions_text]" class="form-control"
                                                       value="{{ old('contact_page.branches.directions_text', $cpBr['directions_text'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= CONTACT PAGE CTA ================= --}}
                        <div class="card mb-2">
                            <div class="card-header" data-toggle="collapse" data-target="#sec-cp-cta" style="cursor:pointer">
                                <h5 class="mb-0">📄 Contact Page — CTA Banner</h5>
                            </div>
                            <div id="sec-cp-cta" class="collapse">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Heading</strong></label>
                                                <input type="text" name="contact_page[cta][heading]" class="form-control"
                                                       value="{{ old('contact_page.cta.heading', $cpCta['heading'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Description</strong></label>
                                                <input type="text" name="contact_page[cta][description]" class="form-control"
                                                       value="{{ old('contact_page.cta.description', $cpCta['description'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label><strong>Button Text</strong></label>
                                                <input type="text" name="contact_page[cta][button_text]" class="form-control"
                                                       value="{{ old('contact_page.cta.button_text', $cpCta['button_text'] ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="form-group">
                                                <label><strong>Button Link</strong> <small class="text-muted">(blank = shop home)</small></label>
                                                <input type="text" name="contact_page[cta][button_link]" class="form-control"
                                                       value="{{ old('contact_page.cta.button_link', $cpCta['button_link'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ================= SUBMIT (SINGLE BUTTON) ================= --}}
                        <div class="form-group mt-4 mb-0">
                            <input type="submit" value="Save All Changes" class="btn btn-primary">
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script type="text/javascript">
$(function () {
    // ── Expand / Collapse All ────────────────────────────────
    $('#expand-all').on('click', function () {
        $('.collapse').collapse('show');
    });
    $('#collapse-all').on('click', function () {
        $('.collapse').collapse('hide');
    });

    // ── Hours repeater ───────────────────────────────────────
    $('#add-hours').on('click', function () {
        var row = $(
            '<div class="form-row mb-2 hours-row">' +
                '<div class="col-md-4"><input type="text" name="hours_keys[]" class="form-control" placeholder="e.g. Weekday"></div>' +
                '<div class="col-md-7"><input type="text" name="hours_values[]" class="form-control" placeholder="e.g. 8a.m – 10p.m"></div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-hours">×</button></div>' +
            '</div>'
        );
        $('#hours-repeater').append(row);
    });
    $(document).on('click', '.remove-hours', function () {
        if ($('.hours-row').length > 1) {
            $(this).closest('.hours-row').remove();
        } else {
            $(this).closest('.hours-row').find('input').val('');
        }
    });

    // ── Hero stats repeater ──────────────────────────────────
    function nextStatIndex() {
        return $('.stat-row').length;
    }
    $('#add-stat').on('click', function () {
        var i = nextStatIndex();
        var row = $(
            '<div class="form-row mb-2 stat-row">' +
                '<div class="col-md-3"><input type="text" name="homepage[hero][stats][' + i + '][value]" class="form-control" placeholder="5,000+"></div>' +
                '<div class="col-md-8"><input type="text" name="homepage[hero][stats][' + i + '][label]" class="form-control" placeholder="Quality products"></div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-sm btn-danger remove-stat">×</button></div>' +
            '</div>'
        );
        $('#stats-repeater').append(row);
    });
    $(document).on('click', '.remove-stat', function () {
        if ($('.stat-row').length > 1) {
            $(this).closest('.stat-row').remove();
        } else {
            $(this).closest('.stat-row').find('input').val('');
        }
    });
});
</script>
@endpush
