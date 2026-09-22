    <footer class="store-footer">
        <div class="container footer-grid">
            <div>
                <h4>ARK AND STAR ENTERPRISE</h4>
                <p>Affordable, reliable and durable tools, building materials and expert support for contractors, tradespeople and home projects.</p>
               @php
    // Remove spaces, +, and dashes to format for wa.me
    $whatsappNumber = preg_replace('/[^0-9]/', '', $setting->phone ?? '233245168718');
    // Optional: Pre-fill a default message when they open the chat
    $whatsappMessage = urlencode("Hello " . ($setting->company_name ?? 'ARK AND STAR') . ", I would like to make an inquiry.");
@endphp

<a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="whatsapp-link">
    Chat with us on WhatsApp
</a>
            </div>
            <div>
            <div>
                <h4>SHOP</h4>
                <ul>
                    @php
                        // Fetch active categories, limit to 5 so it doesn't break the footer layout
                        $footerCategories = \App\Models\Category::where('is_active', true)
                            ->orderBy('name')
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @foreach($footerCategories as $category)
                        <!-- Generate the slug exactly like the controller does -->
                        <li>
                            <a href="{{ route('shop.index') }}?category={{ \Illuminate\Support\Str::slug($category->name) }}">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
                        </div>
            <div>
                <h4>CONTACT US</h4>
                <ul>
                    <li>Spintex Road, Accra</li>
                    <li>Monday–Saturday: 8:00 AM–6:00 PM</li>
                    <li>+233 24 516 8718</li>
                </ul>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© {{ date('Y') }} ARK AND STAR ENTERPRISE. All rights reserved.</span>
            <span>Powered by Msoft Ghana</span>
        </div>
    </footer>