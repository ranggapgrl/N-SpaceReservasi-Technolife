<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin | Needs Study Space</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --crimson: #b1002c;
            --crimson-dark: #8c0022;
            --charcoal: #1b1c1c;
            --body: #4A4A4A;
            --muted: #7A7A7A;
            --surface: #FBF9F8;
            --border: #E5E5E5;
            --card-border: #EAEAEA;
            --error: #BA1A1A;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0; font-family: 'Inter', sans-serif; color: var(--body);
            line-height: 1.6; background: var(--surface); min-height: 100vh;
        }
        .font-heading { font-family: 'Poppins', sans-serif; font-weight: 600; }

        /* Tata letak halaman (CSS biasa, tidak bergantung pada Tailwind) */
        .page {
            position: relative; display: flex; flex-direction: column;
            min-height: 100vh; overflow: hidden;
        }
        .page-header { position: relative; padding: 24px 32px; width: 100%; max-width: 1280px; margin: 0 auto; }
        .brand { margin: 0; font-size: 20px; color: var(--charcoal); }
        .brand span { color: var(--crimson); }
        .page-main {
            position: relative; flex: 1; display: flex;
            align-items: center; justify-content: center; padding: 24px 16px;
        }
        .page-footer { padding: 24px; text-align: center; font-size: 12px; color: #9A9A9A; }
        .deco {
            position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%);
            pointer-events: none;
        }
        @media (max-width: 1023px) { .deco { display: none; } }

        /* Card: dibatasi lebarnya */
        .card {
            width: 100%; max-width: 380px; padding: 32px 28px;
            background: #fff; border: 1px solid var(--card-border); border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
        .card-head { display: flex; flex-direction: column; align-items: center; text-align: center; }

        /* Logo */
        .logo { display: block; width: 88px; height: 88px; object-fit: contain; }
        .card-title { margin: 12px 0 0; font-size: 22px; line-height: 1.3; color: var(--charcoal); }
        .card-sub { margin: 6px 0 0; font-size: 12px; color: #6b7280; line-height: 1.5; }

        .alert {
            margin-top: 16px; padding: 10px 14px; font-size: 12px; color: #93000A;
            background: #FFDAD6; border-left: 4px solid var(--error); border-radius: 12px;
        }

        form { margin-top: 24px; display: flex; flex-direction: column; gap: 14px; }
        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
        .hidden { display: none; }

        .field-wrap { position: relative; }
        .field-wrap > .lead {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; color: var(--muted); pointer-events: none;
        }
        .field {
            width: 100%; height: 44px; border-radius: 12px;
            border: 1px solid var(--border); background: #fff;
            padding: 0 16px 0 42px; font-size: 14px; font-family: inherit;
            color: var(--charcoal); outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field.has-toggle { padding-right: 44px; }
        .field::placeholder { color: #9A9A9A; }
        .field:hover { border-color: #d1d1d1; }
        .field:focus { border-color: var(--crimson); box-shadow: 0 0 0 3px rgba(177, 0, 44, 0.1); }
        .field[aria-invalid="true"] { border-color: var(--error); }
        .field-wrap:focus-within > .lead { color: var(--crimson); }

        .toggle {
            position: absolute; top: 0; bottom: 0; right: 0; width: 44px;
            display: grid; place-items: center; border: 0; background: transparent;
            border-radius: 0 12px 12px 0; color: var(--muted); cursor: pointer;
        }
        .toggle:hover { color: var(--charcoal); }
        .toggle svg { width: 18px; height: 18px; }

        .remember { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #4b5563; }
        .remember input { width: 16px; height: 16px; accent-color: var(--crimson); cursor: pointer; margin: 0; }
        .remember label { cursor: pointer; user-select: none; }

        .btn-primary {
            width: 100%; height: 44px; margin-top: 4px; border-radius: 12px; border: 0;
            background: var(--crimson); color: #fff; font-size: 14px; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: background-color .15s, transform .1s;
        }
        .btn-primary:hover { background: var(--crimson-dark); }
        .btn-primary:active { transform: scale(0.99); }
        .btn-primary:focus-visible, .toggle:focus-visible { outline: 2px solid var(--crimson); outline-offset: 2px; }
    </style>
</head>
<body>

    <div class="page">

        <!-- Lingkaran dekoratif latar belakang -->
        <svg class="deco" width="1000" height="1000" viewBox="0 0 1200 1200" fill="none" aria-hidden="true">
            <circle cx="600" cy="600" r="320" stroke="#f1d4d8" stroke-opacity=".45"/>
            <circle cx="600" cy="600" r="440" stroke="#f1d4d8" stroke-opacity=".30"/>
        </svg>

        <header class="page-header">
            <p class="font-heading brand"><b>Needs Study <span>Space</span></b></p>
        </header>

        <main class="page-main">
            <div class="card">

                <div class="card-head">
                    {{-- Simpan file logo di public/images/logo.png --}}
                    <img src="{{ asset('images/logo-technolife.png') }}" alt="Logo Technolife" class="logo">

                    <h1 class="font-heading card-title">Masuk ke panel admin</h1>
                    <p class="card-sub">Kelola booking, ketersediaan, dan verifikasi KTM N-Space.</p>
                </div>

                @if($errors->any())
                <div role="alert" class="alert">
                    {{ $errors->first() }}
                </div>
                @endif

                <form action="{{ route('admin.login.post') }}" method="POST">
                    @csrf

                    <div class="field-wrap">
                        <label for="email" class="sr-only">Email</label>
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>
                        </svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username"
                               @if($errors->any()) aria-invalid="true" @endif
                               class="field">
                    </div>

                    <div class="field-wrap">
                        <label for="password" class="sr-only">Kata sandi</label>
                        <svg class="lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" placeholder="Kata sandi" required autocomplete="current-password"
                               class="field has-toggle">
                        <button type="button" id="toggle-password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi" class="toggle">
                            <svg id="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="icon-eye-off" class="hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 3l18 18"/><path d="M10.6 6.1A10.6 10.6 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.2 3.8M6.5 7.6C3.9 9.3 2 12 2 12s3.5 7 10 7c1.7 0 3.2-.4 4.5-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                        </button>
                    </div>

                    <div class="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Ingat saya</label>
                    </div>

                    <button type="submit" class="btn-primary">Masuk</button>
                </form>
            </div>
        </main>

        <footer class="page-footer">
            &copy; {{ date('Y') }} Needs Study Space. All rights reserved.
        </footer>
    </div>

    <script>
        // Tampilkan / sembunyikan kata sandi
        (function () {
            var btn = document.getElementById('toggle-password');
            var input = document.getElementById('password');
            var eye = document.getElementById('icon-eye');
            var eyeOff = document.getElementById('icon-eye-off');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eye.classList.toggle('hidden', show);
                eyeOff.classList.toggle('hidden', !show);
                btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        })();
    </script>
</body>
</html>