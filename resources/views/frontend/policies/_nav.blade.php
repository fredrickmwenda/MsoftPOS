<nav class="policy-nav">
    <a href="{{ route('policies.terms') }}"       class="{{ Route::is('policies.terms') ? 'active' : '' }}">Terms</a>
    <a href="{{ route('policies.privacy') }}"      class="{{ Route::is('policies.privacy') ? 'active' : '' }}">Privacy</a>
    <a href="{{ route('policies.returns') }}"     class="{{ Route::is('policies.returns') ? 'active' : '' }}">Returns</a>
    <a href="{{ route('policies.shipping') }}"    class="{{ Route::is('policies.shipping') ? 'active' : '' }}">Shipping</a>
    <a href="{{ route('policies.cancellation') }}" class="{{ Route::is('policies.cancellation') ? 'active' : '' }}">Cancellation</a>
</nav>