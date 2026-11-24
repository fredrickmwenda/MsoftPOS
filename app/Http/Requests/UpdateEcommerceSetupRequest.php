<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEcommerceSetupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [

            'contact_page.hero.heading'                => ['required', 'string', 'max:150'],
            'contact_page.hero.description'            => ['nullable', 'string', 'max:500'],

            'contact_page.info_cards.visit_label'      => ['nullable', 'string', 'max:50'],
            'contact_page.info_cards.call_label'       => ['nullable', 'string', 'max:50'],
            'contact_page.info_cards.email_label'      => ['nullable', 'string', 'max:50'],
            'contact_page.info_cards.hours_label'      => ['nullable', 'string', 'max:50'],

            'contact_page.form.heading'                => ['required', 'string', 'max:150'],
            'contact_page.form.sub'                    => ['nullable', 'string', 'max:300'],
            'contact_page.form.submit_text'            => ['required', 'string', 'max:50'],

            'contact_page.map.heading'                 => ['required', 'string', 'max:150'],
            'contact_page.map.sub'                     => ['nullable', 'string', 'max:300'],
            'contact_page.map.embed_url'               => ['nullable', 'string', 'max:2000'],

            'contact_page.hours.weekdays_label'        => ['nullable', 'string', 'max:50'],
            'contact_page.hours.saturday_label'        => ['nullable', 'string', 'max:50'],
            'contact_page.hours.sunday_label'          => ['nullable', 'string', 'max:50'],
            'contact_page.hours.saturday_close'        => ['nullable', 'string', 'max:20'],
            'contact_page.hours.info_top_label'        => ['nullable', 'string', 'max:50'],
            'contact_page.hours.info_sunday_note'      => ['nullable', 'string', 'max:50'],

            'contact_page.branches.heading'            => ['nullable', 'string', 'max:150'],
            'contact_page.branches.sub'                => ['nullable', 'string', 'max:300'],
            'contact_page.branches.badge_text'         => ['nullable', 'string', 'max:30'],
            'contact_page.branches.directions_text'    => ['nullable', 'string', 'max:50'],

            'contact_page.cta.heading'                 => ['nullable', 'string', 'max:150'],
            'contact_page.cta.description'             => ['nullable', 'string', 'max:300'],
            'contact_page.cta.button_text'             => ['nullable', 'string', 'max:50'],
            'contact_page.cta.button_link'             => ['nullable', 'string', 'max:255'],
        ];
    }
}
