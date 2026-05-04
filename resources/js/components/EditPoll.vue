<script setup>
import { ref } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';

const props = defineProps({
    poll: { type: Object, required: true },
});

const emit = defineEmits(['cancel', 'updated']);
const { fetchApi } = useFetchApi();

const form = ref({
    title:                  props.poll.title || '',
    question:               props.poll.question || '',
    options:                props.poll.options?.map(o => o.label) || ['', ''],
    allow_multiple_choices: props.poll.allow_multiple_choices || false,
    results_public:         props.poll.results_public || false,
    ends_at:                props.poll.ends_at || '',
    start_now:              false,
    scheduled_at:           '',
});

const error = ref(null);
const loading = ref(false);

function addOption() {
    form.value.options.push('');
}

function removeOption(index) {
    if (form.value.options.length <= 2) return;
    form.value.options.splice(index, 1);
}

function submitForm() {
    if (!form.value.question) {
        error.value = 'La question est obligatoire.';
        return;
    }
    if (form.value.options.some(o => o.trim() === '')) {
        error.value = 'Toutes les options doivent être remplies.';
        return;
    }

    loading.value = true;
    error.value = null;

    fetchApi({ url: `/polls/${props.poll.id}`, method: 'PUT', data: form.value })
        .then(() => emit('updated'))
        .catch(err => error.value = err?.data?.message || 'Une erreur est survenue.')
        .finally(() => loading.value = false);
}
</script>

<template>
    <div class="max-w-lg">

        <button @click="emit('cancel')" class="mb-4 text-sm text-gray-500 hover:underline">
            ← Retour
        </button>

        <h2 class="text-lg font-semibold mb-4">Modifier le sondage</h2>

        <p v-if="error" class="mb-4 text-red-500 text-sm">{{ error }}</p>

        <div class="flex flex-col gap-6">

            <!-- Titre -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Titre <span class="text-gray-400">(optionnel)</span>
                </label>
                <input
                    v-model="form.title"
                    type="text"
                    placeholder="Ex: Sondage vacances"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <!-- Question -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Question <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.question"
                    type="text"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <!-- Options -->
            <div>
                <label class="block text-sm font-medium mb-2">
                    Options de réponse <span class="text-red-500">*</span>
                </label>
                <div v-for="(option, index) in form.options" :key="index" class="flex gap-2 mb-2">
                    <input
                        v-model="form.options[index]"
                        type="text"
                        :placeholder="`Option ${index + 1}`"
                        class="w-full border rounded px-3 py-2 text-sm"
                    />
                    <button
                        @click="removeOption(index)"
                        :disabled="form.options.length <= 2"
                        class="text-red-400 hover:text-red-600 disabled:opacity-30"
                    >
                        ✕
                    </button>
                </div>
                <button @click="addOption" class="text-sm text-blue-600 hover:underline mt-1">
                    + Ajouter une option
                </button>
            </div>

            <!-- Paramètres -->
            <div class="flex flex-col gap-3">
                <label class="block text-sm font-medium">Paramètres</label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.allow_multiple_choices" />
                    Autoriser plusieurs choix
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.results_public" />
                    Résultats publics
                </label>
            </div>

            <!-- Date de fin -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Date de fin <span class="text-gray-400">(optionnel)</span>
                </label>
                <input
                    v-model="form.ends_at"
                    type="datetime-local"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

            <!-- Démarrage (seulement si encore en brouillon) -->
            <div v-if="poll.is_draft" class="border rounded p-3">
                <label class="block text-sm font-medium mb-2">Démarrage</label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="form.start_now" />
                    Démarrer maintenant
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex gap-2">
                <button
                    @click="submitForm"
                    :disabled="loading"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                </button>
                <button
                    @click="emit('cancel')"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                >
                    Annuler
                </button>
            </div>

        </div>
    </div>
</template>