@php
    $socialLinks = \App\Models\SocialMedia::active()->get();
@endphp

@if($socialLinks->isNotEmpty())
    <div class="social-links" data-context="{{ $context ?? 'default' }}">
        @foreach($socialLinks as $sm)
            <a href="{{ $sm->url }}" class="social-link" target="_blank" rel="noopener noreferrer"
               title="{{ $sm->name }}" aria-label="{{ $sm->name }}"
               style="--brand: {{ $sm->brand_color }}">
                @include('frontend.partials.social-icon', ['icon' => $sm->icon])
            </a>
        @endforeach
    </div>
@endif
