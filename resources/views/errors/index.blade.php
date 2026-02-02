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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .bg-text-giant {
            font-size: 35vw;
            line-height: 1;
            font-weight: 900;
            color: currentColor;
            white-space: nowrap;
            letter-spacing: -0.05em;
        }
        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 56px;
            padding: 0 40px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-white dark:bg-[#0B0F1A] transition-colors duration-500 min-h-screen flex items-center justify-center p-6 overflow-hidden relative">
    
    <!-- Fixed Background Text -->
    <div class="fixed inset-0 flex items-center justify-center pointer-events-none select-none z-0">
        <div class="bg-text-giant text-slate-100 dark:text-slate-900/40 opacity-100">
            {{ $code }}
        </div>
    </div>

    <!-- Content Container -->
    <div class="relative z-10 w-full max-w-xl text-center">
        <!-- Header Section -->
        <div class="mb-10">
            <div class="flex justify-center items-center gap-6 mb-8">
                <div class="h-[2px] w-12 bg-blue-600 dark:bg-blue-500 rounded-full opacity-40"></div>
                <div class="text-[11px] font-black tracking-[0.5em] uppercase text-blue-600 dark:text-blue-500">
                    System Alert
                </div>
                <div class="h-[2px] w-12 bg-blue-600 dark:bg-blue-500 rounded-full opacity-40"></div>
            </div>
            
            <div class="text-8xl sm:text-9xl font-black tracking-tighter text-blue-600 dark:text-blue-500 mb-2">
                {{ $code }}
            </div>
        </div>

        <!-- Main Text -->
        <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight text-slate-900 dark:text-white mb-6">
            {{ $title }}
        </h1>
        
        <p class="text-slate-500 dark:text-slate-400 text-lg sm:text-2xl font-light mb-16 max-w-md mx-auto leading-relaxed">
            {{ $message }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-4 px-6 md:px-0">
            <a href="{{ url('/') }}" class="btn-custom bg-slate-950 dark:bg-white text-white dark:text-slate-950 hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/10 active:scale-95">
                Return to Home
            </a>
            
            @if(auth()->check())
            <form action="{{ route('logout') }}" method="POST" class="flex flex-col sm:inline-flex">
                @csrf
                <button type="submit" class="btn-custom border-2 border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-95">
                    Sign Out
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- System Version (Positioned absolutely at the bottom to avoid pushing content up) -->
    <div class="absolute bottom-12 left-0 right-0 text-center z-10">
        <div class="text-[10px] font-black uppercase tracking-[0.8em] text-slate-300 dark:text-slate-800">
            PROMISE Inventory Core
        </div>
    </div>

</body>
</html>
