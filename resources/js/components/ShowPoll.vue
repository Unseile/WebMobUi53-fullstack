<script setup>
import { ref, watch, onMounted } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';

const props = defineProps({
  poll: { type: Object, default: null },
});
const emit = defineEmits(['cancel', 'updated']);

const { fetchApi } = useFetchApi();

const localPoll = ref(props.poll);
const selection = ref([]);
const error = ref(null);
const loading = ref(false);
const loadingVote = ref(false);
const voted = ref(false);
const results = ref(null);

watch(() => props.poll, (p) => {
  localPoll.value = p;
  selection.value = [];
  voted.value = false;
  results.value = null;
}, { immediate: true });

function getTokenFromUrl() {
  const params = new URLSearchParams(window.location.search);
  if (params.get('token')) return params.get('token');
  const m = window.location.pathname.match(/\/polls\/([^\/\?#]+)/);
  return m ? decodeURIComponent(m[1]) : null;
}

async function loadPollFromToken() {
  const token = getTokenFromUrl();
  if (!token) return;
  loading.value = true;
  error.value = null;
  try {
    const res = await fetchApi({ url: `/polls/${token}`, method: 'GET' });
    localPoll.value = res;
  } catch (err) {
    if (err?.status === 404) error.value = 'Sondage introuvable.';
    else if (err?.status === 401) error.value = 'Vous devez être connecté pour voir ce sondage.';
    else error.value = err?.data?.message || 'Impossible de charger le sondage.';
  } finally {
    loading.value = false;
  }
}

function toggleOption(optionId) {
  if (!localPoll.value) return;
  if (localPoll.value.allow_multiple_choices) {
    const idx = selection.value.indexOf(optionId);
    if (idx === -1) selection.value.push(optionId);
    else selection.value.splice(idx, 1);
  } else {
    selection.value = selection.value[0] === optionId ? [] : [optionId];
  }
}

async function submitVote() {
  if (!localPoll.value) return;
  if (!selection.value.length) {
    error.value = 'Veuillez sélectionner au moins une option.';
    return;
  }
  loadingVote.value = true;
  error.value = null;
  try {
    const res = await fetchApi({ url: `/polls/${localPoll.value.id}/vote`, method: 'POST', data: { options: selection.value } });
    results.value = res;
    voted.value = true;
    emit('updated');
  } catch (err) {
    if (err?.status === 401) error.value = 'Vous devez être connecté pour voter.';
    else error.value = err?.data?.message || 'Une erreur est survenue lors du vote.';
  } finally {
    loadingVote.value = false;
  }
}

onMounted(() => {
  if (!localPoll.value) loadPollFromToken();
});
</script>

<template>
  <div class="max-w-lg">

    <div v-if="loading" class="text-sm text-gray-500">Chargement...</div>
    <p v-if="error" class="mb-4 text-red-500 text-sm">{{ error }}</p>

    <div v-if="localPoll && !voted" class="border rounded p-4">
      <h2 class="text-lg font-semibold mb-2">{{ localPoll.title || 'Sondage' }}</h2>
      <p class="mb-4">{{ localPoll.question }}</p>

      <div class="flex flex-col gap-2 mb-4">
        <label v-for="opt in localPoll.options" :key="opt.id" class="flex items-center gap-3 cursor-pointer">
          <input
            :type="localPoll.allow_multiple_choices ? 'checkbox' : 'radio'"
            :name="'poll-option-' + localPoll.id"
            :checked="selection.includes(opt.id)"
            @change.prevent="toggleOption(opt.id)"
            class="w-4 h-4"
          />
          <span>{{ opt.label }}</span>
        </label>
      </div>

      <div class="flex gap-2">
        <button @click="submitVote" :disabled="loadingVote" class="px-4 py-2 bg-purple-800 text-white rounded hover:bg-purple-700 disabled:opacity-50">
          {{ loadingVote ? 'Envoi...' : 'Voter' }}
        </button>
      </div>
    </div>

    <div v-if="localPoll && voted" class="border rounded p-4">
      <h3 class="text-lg font-semibold mb-2">Résultats</h3>
      <ul class="space-y-2">
        <li v-for="opt in (results ?? localPoll.options)" :key="opt.id" class="flex justify-between">
          <span>{{ opt.label }}</span>
          <span class="text-gray-600">{{ opt.votes_count ?? opt.votes ?? opt.count ?? 0 }} votes</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.break-all { word-break: break-all; }
</style>
