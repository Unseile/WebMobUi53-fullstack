<script setup>
  import { ref } from 'vue';
  import {useFetchApi} from '../composables/useFetchApi';

  defineProps({
    polls: { type: Array, default: () => [] },
  });

  const showTokenModal = ref(false);
  const selectedToken = ref('');
  const {fetchApi} = useFetchApi();
  const emit = defineEmits(['poll-deleted', 'create-poll', 'edit-poll']);

  function fetchDelete(pollId) {
  fetchApi({ url: `/polls/${pollId}`, method: 'DELETE' })
    .then(() => {
      emit('poll-deleted');
    })
    .catch(error => {
      console.error('Error deleting poll:', error);
    }); 
}

function openTokenModal(token) {
  selectedToken.value = token;
  showTokenModal.value = true;
}

function openPollPage(token) {
  window.location.href = `/polls/${token}`;
}

function closeTokenModal() {
  showTokenModal.value = false;
  selectedToken.value = '';
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
        <td><button @click="openTokenModal(poll.secret_token)" class="px-2 cursor-pointer y-1 text-sm bg-purple-800 rounded hover:bg-purple-700">token</button></td>
        <td><button @click="" class="px-2 cursor-pointer y-1 text-sm bg-green-700 rounded hover:bg-green-600">voir</button></td>
      </tr>
    </tbody>
  </table>
  <div v-if="showTokenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded bg-black p-6 shadow-lg">
      <h3 class="mb-4 text-lg font-semibold">Token du sondage</h3>
      <p class="break-all rounded border border-black-800 bg-black-500 p-3 text-sm">
        {{ selectedToken }}
      </p>
      <div class="mt-4 flex justify-end gap-2">
        <button @click="closeTokenModal" class="px-4 py-2 rounded bg-purple-800 hover:bg-purple-700">
          Fermer
        </button>
      </div>
    </div>
  </div>
</template>
