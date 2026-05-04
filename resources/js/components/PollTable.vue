<script setup>
  import {useFetchApi} from '../composables/useFetchApi';

  defineProps({
    polls: { type: Array, default: () => [] },
  });

  const {fetchApi} = useFetchApi();
  const emit = defineEmits(['poll-deleted', 'create-poll', 'edit-poll']);

  function fetchDelete(pollId) {
  fetchApi({ url: `/polls/${pollId}`, method: 'DELETE' })
    .then(() => {
      console.log('Poll deleted successfully');
    })
    .catch(error => {
      console.error('Error deleting poll:', error);
    }); 
}
</script>

<template>
  <button @click="emit('create-poll')" class="px-4 py-2 mb-4 bg-blue-600 text-white rounded hover:bg-blue-700">
                Créer un sondage
  </button>
  <p v-if="polls.length === 0">Aucun sondage.</p>

  <table v-else class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">ID</th>
        <th class="border px-3 py-2">Titre</th>
        <th class="border px-3 py-2">Question</th>
        <th class="border px-3 py-2">Brouillon</th>
        <th class="border px-3 py-2">Debut</th>
        <th class="border px-3 py-2">Fin</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td>
        <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
        <td><button @click="emit('edit-poll', poll)" class="px-2 cursor-pointer">✏️</button></td>
        <td><button @click="fetchDelete(poll.id)" class="px-2 cursor-pointer">🗑️</button></td>
      </tr>
    </tbody>
  </table>
</template>
