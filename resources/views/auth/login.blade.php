<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login - SIM Perpustakaan MTsN 1 Majene</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              "on-error-container": "#93000a",
              "on-background": "#121c2a",
              "tertiary-container": "#636f68",
              "secondary-fixed-dim": "#b7c8e1",
              "secondary": "#505f76",
              "on-tertiary-container": "#e5f2e9",
              "surface-tint": "#006d37",
              "tertiary": "#4b5750",
              "surface-bright": "#f8f9ff",
              "surface-dim": "#d0dbed",
              "surface-variant": "#d9e3f6",
              "secondary-fixed": "#d3e4fe",
              "error-container": "#ffdad6",
              "surface-container-high": "#dee9fc",
              "tertiary-fixed-dim": "#bdcac1",
              "on-primary-fixed-variant": "#005228",
              "surface-container": "#e6eeff",
              "inverse-primary": "#7ada95",
              "surface-container-low": "#eff4ff",
              "surface-container-highest": "#d9e3f6",
              "on-tertiary": "#ffffff",
              "primary-fixed-dim": "#7ada95",
              "primary": "#006130",
              "surface-container-lowest": "#ffffff",
              "inverse-surface": "#27313f",
              "on-error": "#ffffff",
              "primary-container": "#107c41",
              "secondary-container": "#d0e1fb",
              "on-primary": "#ffffff",
              "background": "#f8f9ff",
              "error": "#ba1a1a",
              "on-secondary-fixed-variant": "#38485d",
              "on-primary-container": "#b6ffc5",
              "on-surface-variant": "#3f4940",
              "on-primary-fixed": "#00210c",
              "tertiary-fixed": "#d9e6dd",
              "on-secondary-container": "#54647a",
              "outline-variant": "#becabd",
              "on-secondary-fixed": "#0b1c30"
            },
            borderRadius: {
              DEFAULT: "0.25rem",
              lg: "0.5rem",
              xl: "0.75rem",
              full: "9999px"
            },
            spacing: {
              "stack-sm": "8px",
              "margin-page": "40px",
              "stack-lg": "32px",
              base: "8px",
              "stack-md": "16px",
              gutter: "24px",
              "container-max": "1280px"
            },
            fontFamily: {
              "body-md": ["Inter"],
              "label-caps": ["Inter"],
              "headline-md": ["Inter"],
              "display-lg": ["Inter"],
              "title-sm": ["Inter"],
              "body-sm": ["Inter"]
            },
            fontSize: {
              "body-md": [
                "16px",
                {
                  lineHeight: "24px",
                  fontWeight: "400"
                }
              ],
              "label-caps": [
                "12px",
                {
                  lineHeight: "16px",
                  letterSpacing: "0.05em",
                  fontWeight: "600"
                }
              ],
              "headline-md": [
                "24px",
                {
                  lineHeight: "32px",
                  letterSpacing: "-0.01em",
                  fontWeight: "600"
                }
              ],
              "display-lg": [
                "36px",
                {
                  lineHeight: "44px",
                  letterSpacing: "-0.02em",
                  fontWeight: "700"
                }
              ],
              "title-sm": [
                "18px",
                {
                  lineHeight: "28px",
                  fontWeight: "600"
                }
              ],
              "body-sm": [
                "14px",
                {
                  lineHeight: "20px",
                  fontWeight: "400"
                }
              ]
            }
          }
        }
      }
    </script>
    <style>
        body {
            background-color: #f8f9ff;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
    </style>
</head>
<body class="bg-background min-h-screen flex items-center justify-center p-4">
<main class="w-full max-w-md bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm overflow-hidden flex flex-col">
    <div class="bg-surface-container-low p-margin-page flex flex-col items-center justify-center border-b border-outline-variant">
        <div class="w-20 h-20 bg-primary-container rounded-full flex items-center justify-center mb-4 text-on-primary-container">
            <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">local_library</span>
        </div>
        <h1 class="font-display-lg text-display-lg text-primary text-center mb-2">SIM Perpustakaan</h1>
        <h2 class="font-headline-md text-headline-md text-on-surface-variant text-center">MTsN 1 Majene</h2>
    </div>

    <div class="p-stack-lg flex flex-col gap-gutter">
        @if(session('status'))
            <div class="rounded-lg border border-primary-container bg-surface-container p-4 text-on-secondary-container text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="rounded-lg border border-error-container bg-error-container/80 p-4 text-on-error text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-gutter">
            @csrf

            <div class="flex flex-col gap-base">
                <label class="font-title-sm text-title-sm text-on-surface" for="email">Username / Email</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-outline">person</span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Masukkan username atau email"
                        class="w-full pl-10 pr-4 py-3 rounded-lg border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-shadow font-body-md text-body-md text-on-surface placeholder:text-outline-variant"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-base">
                <label class="font-title-sm text-title-sm text-on-surface" for="password">Password</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-3 text-outline">lock</span>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="w-full pl-10 pr-4 py-3 rounded-lg border border-outline-variant bg-surface focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-shadow font-body-md text-body-md text-on-surface placeholder:text-outline-variant"
                    />
                </div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" class="rounded border-outline-variant text-primary focus:ring-primary w-4 h-4" />
                    <label class="font-body-sm text-body-sm text-on-surface-variant" for="remember">Ingat Saya</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="font-body-sm text-body-sm text-primary hover:underline" href="{{ route('password.request') }}">Lupa Password?</a>
                @endif
            </div>

            <button type="submit" class="w-full mt-4 bg-primary text-on-primary font-title-sm text-title-sm py-3 px-4 rounded-lg hover:bg-primary-container transition-colors flex items-center justify-center gap-2">
                <span>Masuk Sistem</span>
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">login</span>
            </button>
        </form>
    </div>

    <div class="bg-surface-container-low p-4 text-center border-t border-outline-variant">
        <p class="font-body-sm text-body-sm text-on-surface-variant">© 2024 MTsN 1 Majene. Hak Cipta Dilindungi.</p>
    </div>
</main>
</body>
</html>
