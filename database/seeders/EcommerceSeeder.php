<?php

namespace Database\Seeders;

use App\Models\EcommerceSetup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            'identity' => [
                'logo' => 'logo.png',
                'description'=> '',
                'name' => 'Ark And Star Enterprise'
            ],
            'contact'  => [
                'email'         => 'info@arkandstar.com',
                'email_two' => '',
                'support_email' => 'support@arkandstar.com',
                'phone'         => '+1 555 0100',
                'whatsapp'      => '+1 555 0100',
            ],
            'address'  => [
                'location' => 'Legon, Baba Junction.',
                'location_two' => 'Abokobi, School Junction'
            ],
            'hours'    => [
                'Weekday' => '8a.m- 10p.m',
                'Saturday' => '9a.m - 10p.m'
            ],
            'commerce' => [ /* ... */ ],
            'homepage' => [

                'hero' => [
                    'badge'            => null,   // null → falls back to $siteTitle from GeneralSetting
                    'title_prefix'     => 'Everything you need to',
                    'title_highlight'  => 'build better.',
                    'description'      => 'Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.',
                    'primary_btn_text' => 'Shop products',
                    'primary_btn_link' => \Route::has('all.products') ? route('all.products') : '/products',
                    'secondary_btn_text'=> 'Request a quote',
                    'secondary_btn_link'=> '#categories',
                    'stats' => [
                        ['value' => '5,000+',    'label' => 'Quality products'],
                        ['value' => '24–48 hrs', 'label' => 'Accra delivery'],
                        ['value' => '100%',      'label' => 'Genuine brands'],
                    ],
                    'warranty_title'    => 'Genuine products',
                    'warranty_subtitle' => 'Manufacturer warranty',
                ],
                'deal' => [
                    'label'       => "THIS WEEK'S DEAL",
                    'heading'     => 'Power up your projects.',
                    'description' => 'Save on selected power tools, hand tools and essential site equipment.',
                    'link_text'   => 'Explore special offers →',
                    'link_url'    => \Route::has('all.products') ? route('all.products') : '/products',
                    'badge_top'   => 'UP TO',
                    'badge_value' => '25%',
                    'badge_bottom'=> 'OFF',
                ],

                'featured' => [
                    'eyebrow' => 'SELECTED FOR YOU',
                    'heading' => 'Featured products',
                ],
            ],
            'contact_page' => [
                'hero' => [
                    'heading'     => 'Get in Touch',
                    'description' => "We'd love to hear from you. Whether you have a question about a product, need help with an order, or just want to say hello - our team is here to help.",
                ],
                'info_cards' => [
                    'visit_label' => 'Visit Us',
                    'call_label'  => 'Call Us',
                    'email_label' => 'Email Us',
                    'hours_label' => 'Open Hours',
                ],
                'form' => [
                    'heading'     => 'Send Us a Message',
                    'sub'         => "Fill out the form below and we'll get back to you as soon as possible.",
                    'submit_text' => 'Send Message',
                ],
                'map' => [
                    'heading'   => 'Find Us on the Map',
                    'sub'       => 'Visit our main office or get in touch using the details below.',
                    'embed_url' => null,   // optional: paste a Google Maps embed URL to override auto-generated
                ],
                'hours' => [
                    'weekdays_label'   => 'Monday - Friday',
                    'saturday_label'   => 'Saturday',
                    'sunday_label'     => 'Sunday',
                    'saturday_close'   => '2:00 PM',
                    'info_top_label'   => 'Mon - Saturday',
                    'info_sunday_note' => 'Sunday: Closed',
                ],
                'branches' => [
                    'heading'         => 'Our Branches',
                    'sub'             => 'Find a branch near you. Each location is fully stocked and ready to serve you.',
                    'badge_text'      => 'Branch',
                    'directions_text' => 'Get Directions',
                ],
                'cta' => [
                    'heading'     => 'Ready to Start Shopping?',
                    'description' => 'Browse our full catalogue and place your order online - pay with Paystack or pay on delivery.',
                    'button_text' => 'Shop Now',
                    'button_link' => null,   // null → falls back to route('shop.index')
                ],
            ],
        ];


        foreach ($defaults as $key => $value) {
            EcommerceSetup::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
