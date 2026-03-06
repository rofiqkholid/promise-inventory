@php
    $code = $exception->getStatusCode();
    $title = match ($code) {
        401 => 'Unauthorized',
        403 => 'Access Denied',
        404 => 'Page Not Found',
        419 => 'Page Expired',
        429 => 'Too Many Requests',
        500 => 'Server Error',
        503 => 'Service Unavailable',
        default => 'An Error Occurred',
    };

    $message = match ($code) {
        401 => 'Authentication is required to access this resource.',
        403 => $exception->getMessage() ?: "You don't have permission to access this area.",
        404 => "The path you're looking for doesn't exist.",
        419 => 'The security token has expired. Please try again.',
        429 => "You've made too many requests. Please slow down.",
        500 => "Something went wrong on our end. We're on it.",
        503 => "The server is temporarily down for maintenance.",
        default => 'An unexpected error has occurred.',
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $code }} | {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    @vite(['resources/css/app.css'])

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { 
            font-family: 'Outfit', sans-serif; 
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .bg-text-giant {
            font-size: 35vw;
            line-height: 1;
            font-weight: 900;
            white-space: nowrap;
            letter-spacing: -0.05em;
            color: var(--primary-600);
            opacity: 0.05; /* Sangat tipis */
        }
        .dark .bg-text-giant {
            opacity: 0.03;
        }
        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 0 32px;
            border-radius: 2px; /* rounded-xs */
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s ease;
            letter-spacing: 0.025em;
        }
        .error-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 600px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-white dark:bg-[#0B0F1A] transition-colors duration-500 min-h-screen flex items-center justify-center p-6 relative">
    
    <!-- Fixed Background Text -->
    <div class="fixed inset-0 flex items-center justify-center pointer-events-none select-none" style="z-index: 0;">
        <div class="bg-text-giant">
            {{ $code }}
        </div>
    </div>

    <!-- Content Container -->
    <div class="error-container">
        <!-- Header Section -->
        <div class="mb-10">
            <div class="flex justify-center items-center gap-6 mb-8">
                <div class="h-[2px] w-12 bg-primary-600/40 rounded-full"></div>
                <div class="text-[11px] font-black tracking-[0.5em] uppercase text-primary-600 dark:text-primary-500">
                    System Alert
                </div>
                <div class="h-[2px] w-12 bg-primary-600/40 rounded-full"></div>
            </div>
            
            <div class="text-8xl sm:text-9xl font-black tracking-tighter text-primary-600 dark:text-primary-500 mb-2">
                {{ $code }}
            </div>
        </div>

        <!-- Main Text -->
        <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6">
            {{ $title }}
        </h1>
        
        <p class="text-slate-500 dark:text-slate-400 text-lg sm:text-2xl font-light mb-12 max-w-md mx-auto leading-relaxed">
            {{ $message }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="btn-custom bg-primary-600 hover:bg-primary-700 text-white active:scale-[0.98]">
                Return to Home
            </a>
            
            @if(auth()->check())
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn-custom border-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98]">
                    Sign Out
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- System Version -->
    <div class="absolute bottom-12 left-0 right-0 text-center" style="z-index: 10;">
        <div class="text-[10px] font-black uppercase tracking-[0.8em] text-slate-300 dark:text-slate-800">
            PROMISE Inventory Core
        </div>
    </div>

</body>
</html>
