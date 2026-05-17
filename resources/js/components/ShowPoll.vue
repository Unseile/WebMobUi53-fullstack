<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';
import { usePolling } from '../composables/usePolling';

const props = defineProps({
  poll: { type: Object, default: null },
  isAuthenticated: { type: Boolean, default: false },
  loginUrl: { type: String, default: null },
  currentUserId: { type: Number, default: null },
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

const isPollExpired = computed(() => {
    if (!localPoll.value?.ends_at) return false;
    return new Date(localPoll.value.ends_at) < new Date();
});

const isOwner = computed(() => {
    return props.currentUserId && localPoll.value?.user_id == props.currentUserId;
});

watch(() => props.poll, (p) => {
    localPoll.value = p;
    selection.value = [];
    voted.value = p?.already_voted ?? false;
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
        if (res.already_voted) voted.value = true;
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

async function refreshResults() {
    if (!localPoll.value) return;
    
    if (!localPoll.value.results_public && !voted.value && !isOwner.value) return;

    try {
        const options = await fetchApi({ 
            url: `/polls/${localPoll.value.id}/results`, 
            method: 'GET' 
        });
        if (voted.value) results.value = options;
        else localPoll.value.options = options;
    } catch (err) {
        console.error('Erreur lors du rafraîchissement', err);
    }
}

function getPercentage(option, options) {
    const total = options.reduce((sum, opt) => sum + (opt.votes_count ?? 0), 0);
    if (total === 0) return 0;
    return Math.round((option.votes_count ?? 0) / total * 100);
}

usePolling(refreshResults, 2000);

onMounted(() => {
  if (!localPoll.value) loadPollFromToken();
});
</script>

<template>
  <div class="max-w-lg">

    <div v-if="loading" class="text-sm text-gray-500">Chargement...</div>
    <p v-if="error" class="mb-4 text-red-500 text-sm">{{ error }}</p>

    <div v-if="localPoll" class="border rounded p-4">
      <h2 class="text-lg font-semibold mb-2">{{ localPoll.title || 'Sondage' }}</h2>
      <p class="mb-4">{{ localPoll.question }}</p>

      <!-- Sondage expiré -->
      <div v-if="isPollExpired" class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-600">
          Ce sondage est terminé depuis le {{ new Date(localPoll.ends_at).toLocaleString('fr-CH') }}.
          Il n'est plus possible de voter.
      </div>

      <!-- Message déjà voté -->
      <div v-else-if="voted && !results" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-600">
          Vous avez déjà participé à ce sondage.
      </div>

      <!-- Formulaire de vote — authentifié, pas créateur, pas encore voté, pas expiré -->
      <div v-else-if="props.isAuthenticated && !isOwner && !voted">
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
        <button @click="submitVote" :disabled="loadingVote" class="px-4 py-2 bg-purple-800 text-white rounded hover:bg-purple-700 disabled:opacity-50">
          {{ loadingVote ? 'Envoi...' : 'Voter' }}
        </button>
      </div>

      <!-- Non authentifié — lien connexion -->
      <div v-else-if="!props.isAuthenticated">
        <p class="text-sm text-gray-500 mb-4">
          <a :href="loginUrl" class="text-blue-600 hover:underline">Connectez-vous</a> pour participer au vote.
        </p>
      </div>

      <!-- Résultats — après vote OU résultats publics OU créateur (pas avant vote si authentifié) -->
      <div v-if="isOwner || voted || (localPoll.results_public && (!props.isAuthenticated || voted))">
        <ul class="space-y-3">
          <li v-for="opt in (results ?? localPoll.options)" :key="opt.id">
            <div class="flex justify-between text-sm mb-1">
              <span>{{ opt.label }}</span>
              <span class="text-gray-500">
                {{ opt.votes_count ?? 0 }} votes ({{ getPercentage(opt, results ?? localPoll.options) }}%)
              </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4">
              <div
                class="bg-purple-600 h-4 rounded-full transition-all duration-500"
                :style="{ width: getPercentage(opt, results ?? localPoll.options) + '%' }"
              ></div>
            </div>
          </li>
        </ul>
      </div>

    </div>

  </div>
</template>

<style scoped>
.break-all { word-break: break-all; }
</style>
