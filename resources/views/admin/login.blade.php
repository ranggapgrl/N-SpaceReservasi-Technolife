<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | N-Space</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-n-bg font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-sm border border-n-border p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-n-primary tracking-tight">Needy<span class="text-n-accent">.</span> Admin</h1>
            <p class="text-sm text-n-gray mt-1">Please sign in to continue</p>
        </div>

        @if($errors->any())
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border-2 border-n-border rounded-xl p-3 focus:ring-0 focus:border-n-primary outline-none bg-n-bg/50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-n-charcoal mb-2">Password</label>
                    <input type="password" name="password" required class="w-full border-2 border-n-border rounded-xl p-3 focus:ring-0 focus:border-n-primary outline-none bg-n-bg/50">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="text-n-primary border-n-border rounded">
                    <label for="remember" class="ml-2 text-sm text-n-gray">Remember me</label>
                </div>
                <button type="submit" class="w-full bg-n-primary hover:bg-n-primary-dark text-white py-4 rounded-xl font-semibold transition-colors shadow-md mt-4">
                    Sign In
                </button>
            </div>
        </form>
        
        <div class="mt-6 text-center text-xs text-n-gray">
            Test account: admin@needyspace.com / password
        </div>
    </div>

</body>
</html>
