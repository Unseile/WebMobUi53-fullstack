<script setup>
  import { ref } from 'vue';
  import {useFetchApi} from '../composables/useFetchApi';
  import { formatLocalDateTime } from '../composables/useDateFormat';

  defineProps({
    polls: { type: Array, default: () => [] },
  });

  const showTokenModal = ref(false);
  const selectedToken = ref('');
  const {fetchApi} = useFetchApi();
  const copied = ref(false);
  const deleteError = ref(null);
  const emit = defineEmits(['poll-deleted', 'create-poll', 'edit-poll', 'show-poll']);

  function fetchDelete(pollId) {
    fetchApi({ url: `/polls/${pollId}`, method: 'DELETE' })
        .then(() => {
            deleteError.value = null;
            emit('poll-deleted');
        })
        .catch(error => {
            deleteError.value = error?.data?.message || 'Erreur lors de la suppression.';
        });
  }

function openTokenModal(token) {
    selectedToken.value = `${window.location.origin}/polls/${token}`;
    showTokenModal.value = true;
    copied.value = false;
}

function closeTokenModal() {
  showTokenModal.value = false;
  selectedToken.value = '';
}

function copyLink() {
    navigator.clipboard.writeText(selectedToken.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
}

</script>

<template>
  <button @click="emit('create-poll')" class="px-4 py-2 mb-4 bg-purple-800 text-white rounded hover:bg-purple-700">
                Créer un sondage
  </button>
  <p v-if="polls.length === 0">Aucun sondage.</p>

  <p v-if="deleteError" class="mb-4 text-red-500 text-sm">{{ deleteError }}</p>

  <table v-if="polls.length > 0" class="w-full border-collapse text-left">
    <thead>
      <tr>
        <th class="border px-3 py-2">ID</th>
        <th class="border px-3 py-2">Titre</th>
        <th class="border px-3 py-2">Question</th>
        <th class="border px-3 py-2">Brouillon</th>
        <th class="border px-3 py-2">Debut</th>
        <th class="border px-3 py-2">Fin</th>
        <th class="border px-3 py-2">Token</th>
        <th class="border px-3 py-2">Résultats</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="poll in polls" :key="poll.id">
        <td class="border px-3 py-2">{{ poll.id }}</td>
        <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
        <td class="border px-3 py-2">{{ poll.question }}</td>
        <td class="border px-3 py-2">
            <span v-if="poll.is_draft">Brouillon</span>
            <span v-else-if="poll.started_at && new Date(poll.started_at) > new Date()">Planifié</span>
            <span v-else>Actif</span>
        </td>
        <td class="border px-3 py-2">{{ poll.started_at ? formatLocalDateTime(poll.started_at) : '-' }}</td>
        <td class="border px-3 py-2">{{ poll.ends_at ? formatLocalDateTime(poll.ends_at) : '-' }}</td>
        <td class="border px-3 py-2"><button @click="openTokenModal(poll.secret_token)" class="px-2 mx-2 cursor-pointer text-sm bg-purple-800 rounded hover:bg-purple-700">token</button></td>
        <td class="border px-3 py-2"><button @click="emit('show-poll', poll)" class="px-2 mx-2 cursor-pointer text-sm bg-green-700 rounded hover:bg-green-600">voir</button></td>
        <td><button @click="emit('edit-poll', poll)" class="px-2 cursor-pointer">✏️</button></td>
        <td><button @click="fetchDelete(poll.id)" class="px-2 cursor-pointer">🗑️</button></td>
      </tr>
    </tbody>
  </table>
  <div v-if="showTokenModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded bg-gray-800 p-6 shadow-lg">
      <h3 class="mb-4 text-lg font-semibold">Token du sondage</h3>
      <p class="break-all rounded border-black-800 bg-black p-3 text-sm">
        {{ selectedToken }}
      </p>
      <div class="mt-4 flex justify-end gap-2">
          <button @click="copyLink" class="px-4 py-2 rounded bg-green-700 hover:bg-green-600">
              {{ copied ? 'Copié !' : 'Copier le lien' }}
          </button>
          <button @click="closeTokenModal" class="px-4 py-2 rounded bg-purple-800 hover:bg-purple-700">
              Fermer
          </button>
      </div>
    </div>
  </div>
</template>
