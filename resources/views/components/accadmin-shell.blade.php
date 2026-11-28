@props(['title' => 'Account Admin | MCTBS'])

<x-shell :title="$title">
    <x-slot:nav>
        @include('partials.accadmin-nav')
    </x-slot:nav>

    {{ $slot }}
</x-shell>
