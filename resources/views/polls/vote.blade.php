<x-default-layout>
    <x-slot:title>
        Sondage
    </x-slot>

    <div
        id="app"
        data-props="{{ json_encode([
            'token' => $token,
            'isAuthenticated' => $isAuthenticated,
            'loginUrl' => $loginUrl,
        ]) }}"
    ></div>

    @vite(['resources/js/poll-vote.js'])
</x-default-layout>
