<div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-4 md:p-6 bg-white dark:bg-gray-800 overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-sm text-left text-gray-500 dark:text-gray-400']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
