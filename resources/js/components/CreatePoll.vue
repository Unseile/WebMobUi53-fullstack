<script setup>
import { ref } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';

const emit = defineEmits(['cancel', 'created']);

const { fetchApi } = useFetchApi();

const form = ref({
    title: '',
    question: '',
    is_draft: true,
    allow_multiple_choices: false,
    allow_vote_change: false,
    results_public: false,
    duration: null,
});

const error = ref(null);

function submitForm() {
    if (!form.value.question) {
        error.value = 'La question est obligatoire.';
        return;
    }

    error.value = null;

    function submitForm() {
    fetchApi({ url: '/polls', method: 'POST', data: form.value })
        .then(() => emit('created'))
        .catch(err => error.value = err?.data?.message);
    }
}
</script>

<template>
    <div class="max-w-lg">

        <button @click="emit('cancel')" class="mb-4 text-sm text-gray-500 hover:underline">
            ← Retour
        </button>

        <h2 class="text-lg font-semibold mb-4">Créer un sondage</h2>

        <p v-if="error" class="mb-4 text-red-500 text-sm">{{ error }}</p>

        <div class="flex flex-col gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">Titre <span class="text-gray-400">(optionnel)</span></label>
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Ex: Sondage vacances"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Question <span class="text-red-500">*</span></label>
                <input
                    v-model="form.question"
                    type="text"
                    placeholder="Ex: Quelle est votre couleur préférée ?"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Durée <span class="text-gray-400">(en secondes, optionnel)</span></label>
                <input
                    v-model="form.duration"
                    type="number"
                    min="0"
                    placeholder="Ex: 3600 pour 1 heure"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <div class="flex flex-col gap-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.is_draft" />
                    Brouillon
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.allow_multiple_choices" />
                    Choix multiples
                </label>
            </div>

            <div class="flex gap-2 mt-2">
                <button @click="submitForm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                    Valider
                </button>
                <button @click="emit('cancel')" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                    Annuler
                </button>
            </div>

        </div>
    </div>
</template>