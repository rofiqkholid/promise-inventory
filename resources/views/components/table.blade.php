<div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden relative">
    <div class="p-4 bg-white dark:bg-gray-800">
        <table {{ $attributes->merge(['class' => 'custom-table w-full text-left border-collapse']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

