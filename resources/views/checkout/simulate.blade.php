<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | Needy Study Space</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-n-bg text-n-charcoal font-sans antialiased min-h-screen flex flex-col items-center justify-center p-4">

    {{-- Logo --}}
    <div class="mb-8">
        <a href="{{ url('/') }}" class="font-bold text-3xl text-n-primary tracking-tight">
            Needy<span class="text-n-accent">.</span>
        </a>
    </div>

    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-n-border/60 overflow-hidden">
        
        <!-- Header -->
        <div class="border-b border-n-border/40 p-6 text-center">
            <h1 class="font-bold text-lg text-n-charcoal">Secure Checkout</h1>
            <p class="text-n-gray text-xs mt-1 font-light">Simulated Payment Gateway</p>
        </div>

        <div class="p-8">
            <div class="text-center mb-8">
                <p class="text-xs text-n-gray mb-1 uppercase tracking-wider font-semibold">Total Pembayaran</p>
                <h2 class="text-3xl font-bold text-n-charcoal mb-2">Rp{{ number_format($booking->total_price, 0, ',', '.') }}</h2>
                <div class="inline-block bg-n-bg px-3 py-1 rounded-md border border-n-border/50">
                    <p class="text-xs font-mono text-n-gray">ID: {{ $booking->booking_code }}</p>
                </div>
            </div>

            <form action="{{ route('checkout.process', $booking->booking_code) }}" method="POST">
                @csrf
                
                <p class="text-xs font-semibold uppercase tracking-wider mb-3 text-n-charcoal">Metode Pembayaran</p>
                
                <div class="space-y-3 mb-8">
                    <label class="flex items-center p-4 border border-n-border/60 rounded-xl cursor-pointer hover:border-n-charcoal transition-all group">
                        <input type="radio" name="payment_method" value="qris" class="text-n-charcoal focus:ring-n-charcoal w-4 h-4" checked>
                        <span class="ml-3 font-medium text-sm text-n-charcoal group-hover:text-black">QRIS (GoPay, OVO, Dana)</span>
                    </label>
                    <label class="flex items-center p-4 border border-n-border/60 rounded-xl cursor-pointer hover:border-n-charcoal transition-all group">
                        <input type="radio" name="payment_method" value="bank_transfer" class="text-n-charcoal focus:ring-n-charcoal w-4 h-4">
                        <span class="ml-3 font-medium text-sm text-n-charcoal group-hover:text-black">Bank Transfer (BCA, Mandiri, BNI)</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-n-charcoal hover:bg-black text-white py-3.5 rounded-xl font-medium transition-colors text-sm">
                    Bayar & Konfirmasi
                </button>
                
                <div class="mt-5 text-center">
                    <a href="{{ url('/') }}" class="text-xs text-n-gray hover:text-n-charcoal transition-colors underline decoration-n-border underline-offset-4">Batal & Kembali ke Beranda</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="mt-8 text-center text-xs text-n-gray font-light">
        &copy; {{ date('Y') }} Needy Study Space. Secured Transaction.
    </div>

</body>
</html>
