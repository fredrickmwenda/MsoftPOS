<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\GeneralSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display the Contact Us page with:
     *  - Main company address (from GeneralSetting) + Google Map embed
     *  - Contact form
     *  - All active warehouses shown as branch cards with mini-maps
     */
    public function index()
    {
        $generalSetting = GeneralSetting::latest()->first();
        $branches = Warehouse::where('is_active', true)->orderBy('name')->get();

        // Build the main address string for the map embed
        $mainAddress = $this->buildMainAddress($generalSetting);

        return view('frontend.contact-us', compact('generalSetting', 'branches', 'mainAddress'));
    }

    /**
     * Handle the contact form submission.
     * Stores the message in the DB (always) and emails the company
     * (best-effort — email failure doesn't block the user).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Always persist so no message is lost even if email fails
            ContactMessage::create(array_merge($validated, [
                'ip_address' => $request->ip(),
            ]));

            // Best-effort email to the company
            $generalSetting = GeneralSetting::latest()->first();
            $companyEmail = $generalSetting?->email
                ?? config('mail.from.address')
                ?? 'info@example.com';

            $this->sendNotificationEmail($validated, $companyEmail);

            return redirect()
                ->route('contact.us')
                ->with('success', 'Thank you for reaching out! Your message has been received. We will get back to you within 24 hours.');

        } catch (\Throwable $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data'  => $validated,
            ]);

            return redirect()
                ->back()
                ->with('error', 'We could not send your message at the moment. Please try again or call us directly.')
                ->withInput();
        }
    }

    /**
     * Build a single-line address string from GeneralSetting fields
     * so we can pass it to Google Maps embed.
     */
    private function buildMainAddress(?GeneralSetting $gs): string
    {
        if (! $gs) {
            return config('app.name', 'Our Store');
        }

        $parts = array_filter([
            $gs->address ?? $gs->company_address ?? null,
            $gs->city ?? null,
            $gs->state ?? null,
            $gs->country ?? null,
        ]);

        return implode(', ', $parts) ?: ($gs->company_name ?? config('app.name'));
    }

    /**
     * Build a single-line address for a warehouse/branch.
     */
    public static function buildBranchAddress($warehouse): string
    {
        $parts = array_filter([
            $warehouse->address ?? $warehouse->location ?? null,
            $warehouse->city ?? null,
            $warehouse->state ?? null,
            $warehouse->country ?? null,
        ]);

        return implode(', ', $parts) ?: $warehouse->name;
    }

    /**
     * Generate a Google Maps embed URL (no API key needed).
     */
    public static function mapEmbedUrl(string $address): string
    {
        return 'https://maps.google.com/maps?q=' . urlencode($address)
            . '&t=&z=14&ie=UTF8&iwloc=&output=embed';
    }

    /**
     * Generate a Google Maps "directions" link for the user to click.
     */
    public static function mapDirectionsUrl(string $address): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($address);
    }

    private function sendNotificationEmail(array $data, string $companyEmail): void
    {
        try {
            Mail::raw(
                "New Contact Form Submission\n\n"
                . "Name: {$data['name']}\n"
                . "Email: {$data['email']}\n"
                . "Phone: " . ($data['phone'] ?? 'N/A') . "\n"
                . "Subject: {$data['subject']}\n\n"
                . "Message:\n{$data['message']}",
                function ($mail) use ($data, $companyEmail) {
                    $mail->to($companyEmail)
                         ->replyTo($data['email'], $data['name'])
                         ->subject('Contact Form: ' . $data['subject']);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Contact form email delivery failed (message still saved)', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}