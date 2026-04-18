<x-default-layout>
  <x-slot:scripts>
    @vite(['resources/js/poll-builder.js'])
  </x-slot>

  <x-slot:title>
    Nouveau sondage
  </x-slot>

  <div id="app" data-props='@json(["loginUrl" => route("login")])'></div>
</x-default-layout>
