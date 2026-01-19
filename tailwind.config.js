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
            }
        },
    },
    safelist: [
        'dark:bg-blue-900/40',
        'dark:bg-blue-900/30',
        'dark:bg-gray-800',
        'dark:bg-gray-700',
        'dark:text-blue-400',
        'dark:text-green-500',
        'dark:text-blue-500',
        'dark:text-red-500',
        'dark:border-gray-700',
        'dark:border-blue-900/50',
    ],
    plugins: [],
}
