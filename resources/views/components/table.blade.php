<div class="bg-white dark:bg-gray-800 shadow-xl sm:rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
    <div class="p-3 md:p-4 bg-white dark:bg-gray-800">
        <table {{ $attributes->merge(['class' => 'inline-table w-full text-sm text-left text-gray-500 dark:text-gray-400']) }}>
            {{ $slot }}
        </table>
    </div>
</div>
