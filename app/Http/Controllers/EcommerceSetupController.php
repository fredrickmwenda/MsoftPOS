<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EcommerceSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EcommerceSetupController extends Controller
{
    public function edit()
    {
        $settings = EcommerceSetup::allGrouped();

        return view('backend.ecommerce_setup.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // Identity
            'identity.name'        => 'nullable|string|max:255',
            'identity.description' => 'nullable|string|max:500',
            'identity.logo'        => 'nullable|string|max:255',
            'logo_file'            => 'nullable|image|max:2048',

            // Contact
            'contact.email'         => 'nullable|email|max:255',
            'contact.email_two'     => 'nullable|email|max:255',
            'contact.support_email' => 'nullable|email|max:255',
            'contact.phone'         => 'nullable|string|max:50',
            'contact.whatsapp'      => 'nullable|string|max:50',

            // Address
            'address.location'     => 'nullable|string|max:500',
            'address.location_two' => 'nullable|string|max:500',

            // Hours (repeater)
            'hours_keys'          => 'array',
            'hours_keys.*'        => 'nullable|string|max:50',
            'hours_values'        => 'array',
            'hours_values.*'      => 'nullable|string|max:100',

            // Commerce
            'commerce.currency'           => 'nullable|string|max:10',
            'commerce.announcement'       => 'nullable|string|max:255',
            'commerce.free_shipping_over' => 'nullable|numeric|min:0',

            // Homepage — Hero
            'homepage.hero.badge'              => 'nullable|string|max:100',
            'homepage.hero.title_prefix'       => 'nullable|string|max:200',
            'homepage.hero.title_highlight'    => 'nullable|string|max:200',
            'homepage.hero.description'        => 'nullable|string|max:600',
            'homepage.hero.primary_btn_text'   => 'nullable|string|max:50',
            'homepage.hero.primary_btn_link'   => 'nullable|string|max:255',
            'homepage.hero.secondary_btn_text' => 'nullable|string|max:50',
            'homepage.hero.secondary_btn_link' => 'nullable|string|max:255',
            'homepage.hero.warranty_title'     => 'nullable|string|max:100',
            'homepage.hero.warranty_subtitle'  => 'nullable|string|max:150',
            'homepage.hero.stats'              => 'array',
            'homepage.hero.stats.*.value'      => 'nullable|string|max:50',
            'homepage.hero.stats.*.label'      => 'nullable|string|max:100',

            // Homepage — Categories
            'homepage.categories.eyebrow'       => 'nullable|string|max:100',
            'homepage.categories.heading'       => 'nullable|string|max:150',
            'homepage.categories.view_all_text' => 'nullable|string|max:100',

            // Homepage — Deal
            'homepage.deal.label'        => 'nullable|string|max:100',
            'homepage.deal.heading'      => 'nullable|string|max:200',
            'homepage.deal.description'  => 'nullable|string|max:300',
            'homepage.deal.link_text'    => 'nullable|string|max:100',
            'homepage.deal.link_url'     => 'nullable|string|max:255',
            'homepage.deal.badge_top'    => 'nullable|string|max:20',
            'homepage.deal.badge_value'  => 'nullable|string|max:20',
            'homepage.deal.badge_bottom' => 'nullable|string|max:20',

            // Homepage — Featured
            'homepage.featured.eyebrow' => 'nullable|string|max:100',
            'homepage.featured.heading' => 'nullable|string|max:150',

            // Contact Page — Hero
            'contact_page.hero.heading'     => 'nullable|string|max:150',
            'contact_page.hero.description' => 'nullable|string|max:500',

            // Contact Page — Info cards
            'contact_page.info_cards.visit_label' => 'nullable|string|max:50',
            'contact_page.info_cards.call_label'  => 'nullable|string|max:50',
            'contact_page.info_cards.email_label' => 'nullable|string|max:50',
            'contact_page.info_cards.hours_label' => 'nullable|string|max:50',

            // Contact Page — Form
            'contact_page.form.heading'     => 'nullable|string|max:150',
            'contact_page.form.sub'         => 'nullable|string|max:300',
            'contact_page.form.submit_text' => 'nullable|string|max:50',

            // Contact Page — Map
            'contact_page.map.heading'   => 'nullable|string|max:150',
            'contact_page.map.sub'       => 'nullable|string|max:300',
            'contact_page.map.embed_url' => 'nullable|string|max:2000',

            // Contact Page — Hours
            'contact_page.hours.weekdays_label'   => 'nullable|string|max:50',
            'contact_page.hours.saturday_label'   => 'nullable|string|max:50',
            'contact_page.hours.sunday_label'     => 'nullable|string|max:50',
            'contact_page.hours.saturday_close'   => 'nullable|string|max:20',
            'contact_page.hours.info_top_label'   => 'nullable|string|max:50',
            'contact_page.hours.info_sunday_note' => 'nullable|string|max:50',

            // Contact Page — Branches
            'contact_page.branches.heading'         => 'nullable|string|max:150',
            'contact_page.branches.sub'             => 'nullable|string|max:300',
            'contact_page.branches.badge_text'      => 'nullable|string|max:30',
            'contact_page.branches.directions_text' => 'nullable|string|max:50',

            // Contact Page — CTA
            'contact_page.cta.heading'     => 'nullable|string|max:150',
            'contact_page.cta.description' => 'nullable|string|max:300',
            'contact_page.cta.button_text' => 'nullable|string|max:50',
            'contact_page.cta.button_link' => 'nullable|string|max:255',
        ]);

        // Handle logo file upload (overrides the text field)
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $file->move(public_path('logo'), $filename);
            $data['identity']['logo'] = $filename;
        }

        // Build hours map from repeater inputs
        $hoursMap = [];
        $keys   = $request->input('hours_keys', []);
        $values = $request->input('hours_values', []);
        foreach ($keys as $i => $key) {
            $key = trim((string) $key);
            if ($key !== '') {
                $hoursMap[$key] = trim((string) ($values[$i] ?? ''));
            }
        }

        // Save each group
        foreach (['identity', 'contact', 'address', 'commerce', 'homepage', 'contact_page'] as $group) {
            EcommerceSetup::updateOrCreate(
                ['key' => $group],
                ['value' => $data[$group] ?? []]
            );
        }
        EcommerceSetup::updateOrCreate(['key' => 'hours'], ['value' => $hoursMap]);

        Cache::forget('ecommerce_setup.all');
        Cache::forget('ecommerce_setup.view');

        return back()->with('message', 'E-commerce setup saved successfully.');
    }
}
