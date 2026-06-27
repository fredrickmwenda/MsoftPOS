<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display — {{ $general_setting->site_title ?? 'POS' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
        }

        /* Idle / Promo State */
        #idle-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 2rem;
        }
        #idle-state .logo-area {
            margin-bottom: 2rem;
        }
        #idle-state .logo-area img {
            max-width: 220px;
            max-height: 100px;
            object-fit: contain;
        }
        #idle-state h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #13bd60, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        #idle-state .welcome-text {
            font-size: 1.5rem;
            color: #94a3b8;
            font-weight: 400;
        }
        #idle-state .promo-box {
            margin-top: 3rem;
            padding: 1.5rem 3rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            font-size: 1.25rem;
            color: #fbbf24;
        }

        /* Shopping / Cart State */
        #cart-state {
            display: none;
            height: 100vh;
            flex-direction: column;
        }
        .cart-header {
            background: #1e293b;
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f8fafc;
        }
        .cart-header .transaction-info {
            font-size: 0.875rem;
            color: #94a3b8;
        }
        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 2rem;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #334155;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .cart-item .item-info {
            flex: 1;
        }
        .cart-item .item-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: #f1f5f9;
        }
        .cart-item .item-meta {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }
        .cart-item .item-qty {
            font-size: 1rem;
            color: #64748b;
            margin-right: 2rem;
            min-width: 60px;
            text-align: center;
        }
        .cart-item .item-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #13bd60;
            min-width: 120px;
            text-align: right;
        }

        /* Totals Area */
        .totals-area {
            background: #1e293b;
            border-top: 2px solid #334155;
            padding: 1.5rem 2rem;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 1rem;
            color: #cbd5e1;
        }
        .totals-row.grand {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #475569;
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
        }
        .totals-row.grand .amount {
            color: #13bd60;
            font-size: 2.5rem;
        }

        /* Payment State */
        #payment-state {
            display: none;
            height: 100vh;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
        }
        .payment-box {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 24px;
            padding: 3rem 4rem;
            min-width: 500px;
        }
        .payment-label {
            font-size: 1.25rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
        }
        .payment-amount {
            font-size: 5rem;
            font-weight: 800;
            color: #f8fafc;
            margin-bottom: 0.5rem;
        }
        .payment-method {
            font-size: 1.5rem;
            color: #3b82f6;
            margin-bottom: 2rem;
        }
        .change-box {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #475569;
        }
        .change-label {
            font-size: 1.5rem;
            color: #94a3b8;
        }
        .change-amount {
            font-size: 4rem;
            font-weight: 800;
            color: #13bd60;
        }

        /* Thank You State */
        #thankyou-state {
            display: none;
            height: 100vh;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        #thankyou-state h1 {
            font-size: 4rem;
            font-weight: 800;
            color: #13bd60;
            margin-bottom: 1rem;
        }
        #thankyou-state p {
            font-size: 1.5rem;
            color: #94a3b8;
        }
        .receipt-icon {
            width: 120px;
            height: 120px;
            background: rgba(19, 189, 96, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            font-size: 3rem;
        }

        /* Connection status */
        .connection-status {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 1000;
        }
        .connection-status.connected {
            background: rgba(19, 189, 96, 0.2);
            color: #13bd60;
            border: 1px solid rgba(19, 189, 96, 0.3);
        }
        .connection-status.disconnected {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        /* Scrollbar styling */
        .cart-items::-webkit-scrollbar {
            width: 8px;
        }
        .cart-items::-webkit-scrollbar-track {
            background: #0f172a;
        }
        .cart-items::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div id="connectionStatus" class="connection-status disconnected">Waiting for POS...</div>

    <!-- IDLE STATE -->
    <div id="idle-state">
        <div class="logo-area">
            @if($general_setting->site_logo)
                <img src="{{ asset('images/msoft.png') }}" alt="Logo">
            @else
                <h1>{{ $general_setting->site_title }}</h1>
            @endif
        </div>
        <div class="welcome-text">Welcome! Please proceed to checkout.</div>
        <div class="promo-box">
            🎉 Thank you for shopping with us!
        </div>
    </div>

    <!-- CART STATE -->
    <div id="cart-state">
        <div class="cart-header">
            <h2>🛒 Your Order</h2>
            <div class="transaction-info">
                <span id="cart-ref">Ref: —</span> &nbsp;|&nbsp; 
                <span id="cart-customer">Customer: —</span>
            </div>
        </div>
        <div class="cart-items" id="cartItems">
            <!-- Items injected here -->
        </div>
        <div class="totals-area">
            <div class="totals-row">
                <span>Items</span>
                <span id="total-items">0</span>
            </div>
            <div class="totals-row">
                <span>Subtotal</span>
                <span id="total-subtotal">0.00</span>
            </div>
            <div class="totals-row">
                <span>Tax</span>
                <span id="total-tax">0.00</span>
            </div>
            <div class="totals-row">
                <span>Discount</span>
                <span id="total-discount">0.00</span>
            </div>
            <div class="totals-row grand">
                <span>AMOUNT DUE</span>
                <span class="amount" id="grand-total-display">0.00</span>
            </div>
        </div>
    </div>

    <!-- PAYMENT STATE -->
    <div id="payment-state">
        <div class="payment-box">
            <div class="payment-label">Please Pay</div>
            <div class="payment-amount" id="payment-amount">0.00</div>
            <div class="payment-method" id="payment-method">Cash</div>
            
            <div class="change-box" id="change-box" style="display:none;">
                <div class="change-label">Change</div>
                <div class="change-amount" id="change-amount">0.00</div>
            </div>
        </div>
    </div>

    <!-- THANK YOU STATE -->
    <div id="thankyou-state">
        <div class="receipt-icon">✓</div>
        <h1>Thank You!</h1>
        <p>Your transaction is complete. Please collect your receipt.</p>
    </div>

    <script>
        // BroadcastChannel for cross-window communication
        const channel = new BroadcastChannel('pos_dual_screen');
        const currencyCode = '{{ $currency->code ?? "USD" }}';
        const decimalPlaces = {{ $general_setting->decimal ?? 2 }};

        let currentState = 'idle';
        let cartData = [];

        function formatMoney(amount) {
            return parseFloat(amount).toFixed(decimalPlaces) + ' ' + currencyCode;
        }

        function showState(state) {
            currentState = state;
            document.querySelectorAll('#idle-state, #cart-state, #payment-state, #thankyou-state').forEach(el => {
                el.style.display = 'none';
            });
            document.getElementById(state + '-state').style.display = 
                (state === 'cart') ? 'flex' : 'block';
            if (state === 'cart') {
                document.getElementById('cart-state').style.display = 'flex';
            }
        }

        function updateConnectionStatus(connected) {
            const status = document.getElementById('connectionStatus');
            if (connected) {
                status.className = 'connection-status connected';
                status.textContent = 'Connected to POS';
            } else {
                status.className = 'connection-status disconnected';
                status.textContent = 'Waiting for POS...';
            }
        }

        function renderCart(items, totals) {
            const container = document.getElementById('cartItems');
            container.innerHTML = '';
            
            items.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'cart-item';
                div.style.animationDelay = (index * 0.05) + 's';
                div.innerHTML = `
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-meta">${item.code}</div>
                    </div>
                    <div class="item-qty">× ${item.qty}</div>
                    <div class="item-price">${formatMoney(item.subtotal)}</div>
                `;
                container.appendChild(div);
            });

            document.getElementById('total-items').textContent = totals.items || 0;
            document.getElementById('total-subtotal').textContent = formatMoney(totals.subtotal || 0);
            document.getElementById('total-tax').textContent = formatMoney(totals.tax || 0);
            document.getElementById('total-discount').textContent = formatMoney(totals.discount || 0);
            document.getElementById('grand-total-display').textContent = formatMoney(totals.grandTotal || 0);
            
            if (totals.ref) document.getElementById('cart-ref').textContent = 'Ref: ' + totals.ref;
            if (totals.customer) document.getElementById('cart-customer').textContent = 'Customer: ' + totals.customer;
        }

        function showPayment(data) {
            showState('payment');
            document.getElementById('payment-amount').textContent = formatMoney(data.amount);
            document.getElementById('payment-method').textContent = data.method || 'Cash';
            
            const changeBox = document.getElementById('change-box');
            if (data.change && parseFloat(data.change) > 0) {
                changeBox.style.display = 'block';
                document.getElementById('change-amount').textContent = formatMoney(data.change);
            } else {
                changeBox.style.display = 'none';
            }
        }

        // Listen for messages from main POS
        channel.addEventListener('message', (event) => {
            const data = event.data;
            updateConnectionStatus(true);

            switch(data.type) {
                case 'cart_update':
                    cartData = data.items || [];
                    renderCart(cartData, data.totals || {});
                    if (currentState !== 'cart') showState('cart');
                    break;

                case 'payment_start':
                    showPayment(data);
                    break;

                case 'payment_complete':
                    showState('thankyou');
                    setTimeout(() => {
                        showState('idle');
                    }, 8000);
                    break;

                case 'cart_clear':
                    showState('idle');
                    break;

                case 'ping':
                    updateConnectionStatus(true);
                    channel.postMessage({ type: 'pong' });
                    break;
            }
        });

        // Heartbeat check
        setInterval(() => {
            if (currentState !== 'idle') {
                // If no message received recently, we could switch to idle
                // But we'll let the POS control the state explicitly
            }
        }, 5000);

        // Initial state
        showState('idle');

        // Handle visibility change (pause/resume)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                channel.postMessage({ type: 'display_ready' });
            }
        });
    </script>
</body>
</html>