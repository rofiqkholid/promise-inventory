/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                background: '#f8fafc', // slate-50 default
                sidebar: '#ffffff',
                primary: {
                    50: 'var(--primary-50)',
                    100: 'var(--primary-100)',
                    200: 'var(--primary-200)',
                    300: 'var(--primary-300)',
                    400: 'var(--primary-400)',
                    500: 'var(--primary-500)',
                    600: 'var(--primary-600)',
                    700: 'var(--primary-700)',
                    800: 'var(--primary-800)',
                    900: 'var(--primary-900)',
                    950: 'var(--primary-950)',
                }
            }
        },
    },
    safelist: [
        'dark:bg-primary-900/40',
        'dark:bg-primary-900/30',
        'dark:bg-gray-800',
        'dark:bg-gray-700',
        'dark:text-primary-400',
        'dark:text-green-500',
        'dark:text-primary-500',
        'dark:text-red-500',
        'dark:border-gray-700',
        'dark:border-primary-900/50',
    ],
    plugins: [],
}
