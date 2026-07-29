<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 border border-transparent font-semibold text-sm text-white hover:bg-indigo-500 focus:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
