<x-default-layout>
  <x-slot:scripts>
    @vite(['resources/js/poll-dashboard-integrated.js'])
  </x-slot>

  <x-slot:title>
    Dashboard des sondages intégré
  </x-slot>

   <div id="app" data-props="{{ json_encode([
    'loginUrl' => route('login'),
    'isAuthenticated' => auth()->check(),
    'currentUserId' => auth()->id(),
    'initialPolls' => $polls ?? [],
  ]) }}"></div>
</x-default-layout>
