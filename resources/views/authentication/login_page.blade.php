<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PROMISE Inventory</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image/favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
        }

        .theme-transition {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
        }
        .divider {
            position: relative;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #cbd5e1, transparent);
        }

        .divider span {
            position: relative;
            background: white;
            padding: 0 0.75rem;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-6xl flex flex-col lg:flex-row gap-0 rounded-3xl shadow-2xl border border-gray-200 overflow-hidden login-card">
        
        <!-- Left Section - Info -->
        <div class="hidden lg:flex lg:w-1/2 bg-white p-12 flex-col justify-between text-gray-900 border-r border-gray-200">
            <div>
                <h2 class="text-3xl font-bold mb-6 leading-tight text-gray-700">Welcome to</h2>
                <h1 class="text-5xl font-bold py-2 bg-gradient-to-r from-blue-900 to-cyan-600 bg-clip-text text-transparent">Promise Inventory</h1>
                <p class="text-gray-600 text-lg mb-12">
                    Project Management Integrated System Engineering
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Efficient Management</h3>
                        <p class="text-gray-600 text-sm">Streamline your project workflow</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Secure Access</h3>
                        <p class="text-gray-600 text-sm">Your data is protected with security</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Real-time Updates</h3>
                        <p class="text-gray-600 text-sm">Get instant notifications and updates</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-gray-600 text-sm">Summit Adyawinsa Indonesia</p>
            </div>
        </div>

        <!-- Right Section - Form -->
        <div class="w-full lg:w-1/2 p-8 space-y-8 bg-white">

            <div class="text-center">
                <img src="{{ asset('assets/image/logo-promise.png') }}" alt="PROMISE Logo" class="mx-auto h-[120px] w-auto mb-4 logo-glow">
            </div>

        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg" role="alert">
            <p class="font-bold text-sm">Authentication Failed</p>
            <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form class="space-y-5" action="{{ route('login_post') }}" method="POST">
            @csrf
            <div class="space-y-5">
                <div>
                    <label for="nik" class="block text-sm font-semibold text-gray-700 mb-2">Employee ID (NIK)</label>
                    <div class="relative mt-1 input-field">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 2a1.5 1.5 0 00-1.5 1.5V5.25a.75.75 0 001.5 0V3.5A1.5 1.5 0 0010 2zM5.25 5.25a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H5.25zM12 8a4 4 0 11-8 0 4 4 0 018 0zM15 11.25a.75.75 0 00-1.5 0v1.5a.75.75 0 001.5 0v-1.5z" clip-rule="evenodd" />
                                <path d="M3 10a7 7 0 1114 0 7 7 0 01-14 0zM10 4a6 6 0 100 12 6 6 0 000-12z" />
                            </svg>
                        </div>
                        <input id="nik" name="nik" type="text" autocomplete="username" required
                            value="{{ old('nik') }}"
                            class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                            placeholder="e.g., 202577-001">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative mt-1 input-field">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="block w-full pl-12 pr-12 py-3 border border-gray-300 rounded-lg placeholder-gray-400 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm"
                            placeholder="••••••••">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                            <button type="button" id="toggle-password" class="text-gray-400 hover:text-blue-500 focus:outline-none transition">
                                <svg id="eye-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                <svg id="eye-slash-icon" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074L3.707 2.293zM10.75 7.5a2.5 2.5 0 00-3.536 3.536l2.5-2.5a1.5 1.5 0 011.036-1.036z" />
                                    <path d="M10 5c.104 0 .207.004.31.011l-1.054 1.054A3.001 3.001 0 007 10c0 .398.076.78.217 1.132l-1.44 1.44A9.963 9.963 0 01.458 10C1.732 5.943 5.522 5 10 5z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
                    <label for="remember" class="ml-2 block text-sm text-gray-700 font-medium cursor-pointer">Remember me</label>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="btn-submit group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-800 to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Login
                </button>
            </div>
            <div>
                <a href="{{ route('forget_password') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Forget Password?</a>
            </div>

            <div class="divider"><span>or</span></div>

            <div class="text-center">
                <p class="text-sm text-gray-600 mb-3">Need access to drawing?</p>
                <a href="/" class="inline-flex items-center justify-center w-full py-2.5 px-4 border border-blue-200 text-blue-600 hover:bg-blue-50 font-semibold rounded-lg transition duration-200">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" />
                    </svg>
                    Promise Drawing
                </a>
            </div>

        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeSlashIcon = document.getElementById('eye-slash-icon');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('hidden', isPassword);
            eyeSlashIcon.classList.toggle('hidden', !isPassword);
        });

        const form = document.querySelector('form');
        const submitBtn = document.querySelector('button[type="submit"]');
        let isSubmitting = false;
        const originalBtnHTML = submitBtn.innerHTML;


        const spinnerSVG = `
    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
    </svg>
  `;

        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                return;
            }
            isSubmitting = true;


            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.setAttribute('aria-busy', 'true');
            submitBtn.setAttribute('aria-disabled', 'true');
            submitBtn.innerHTML = `
      <span class="flex items-center justify-center gap-2">
        ${spinnerSVG}
        <span>Signing in...</span>
      </span>
    `;

        });
    </script>
</body>

</html>