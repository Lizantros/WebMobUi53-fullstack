<x-default-layout>
    <x-slot:title>
        Sondages
    </x-slot>

    <div
        id="app"
        data-props="{{ json_encode([
            'polls' => $polls,
            'loginUrl' => route('login'),
            'username' => 'test name',
        ]) }}"
    ></div>

    @vite(['resources/js/poll-dashboard.js'])
</x-default-layout>
