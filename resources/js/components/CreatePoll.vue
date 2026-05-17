<script setup>
import { ref } from 'vue';
import { useFetchApi } from '../composables/useFetchApi';

const emit = defineEmits(['cancel', 'created']);
const { fetchApi } = useFetchApi();

const form = ref({
    title: '',
    question: '',
    options: ['', ''],
    allow_multiple_choices: false,
    results_public: false,
    ends_at: '',
    start_mode: 'draft',      
    scheduled_at: '',         
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

async function submitForm() {
    if (!form.value.question) {
        error.value = 'La question est obligatoire.';
        return;
    }
    if (form.value.options.some(o => o.trim() === '')) {
        error.value = 'Toutes les options doivent être remplies.';
        return;
    }
    if (form.value.start_mode === 'later' && !form.value.scheduled_at) {
        error.value = 'Veuillez choisir une date de démarrage.';
        return;
    }

    const data = {
        title:                  form.value.title,
        question:               form.value.question,
        options:                form.value.options,
        allow_multiple_choices: form.value.allow_multiple_choices,
        results_public:         form.value.results_public,
        ends_at:                form.value.ends_at || null,
        is_draft:               form.value.start_mode === 'draft',
        start_now:              form.value.start_mode === 'now',
        scheduled_at:           form.value.start_mode === 'later' ? form.value.scheduled_at : null,
    };

    loading.value = true;
    error.value = null;

    try {
        const res = await fetchApi({ url: '/polls', method: 'POST', data });
        const poll = res?.data ?? res;
        emit('created', poll);
    } catch (err) {
        error.value = err?.data?.message || 'Une erreur est survenue.';
    } finally {
        loading.value = false;
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

        <div class="flex flex-col gap-6">

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

            <div>
                <label class="block text-sm font-medium mb-1">
                    Question <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.question"
                    type="text"
                    placeholder="Ex: Quelle est votre couleur préférée ?"
                    class="w-full border rounded px-3 py-2 text-sm"
                />
            </div>

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
                <button @click="addOption" class="text-sm text-purple-700 hover:underline mt-1">
                    + Ajouter une option
                </button>
            </div>

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

            <div>
                <label class="block text-sm font-medium mb-1">
                    Date de fin <span class="text-gray-400">(optionnel)</span>
                </label>
                <input
                    v-model="form.ends_at"
                    type="datetime-local"
                    class="w-full border rounded px-3 py-2 text-sm text-black bg-white"
                />
            </div>

            <div class="border rounded p-3">
                <label class="block text-sm font-medium mb-2">Démarrage</label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" value="draft" v-model="form.start_mode" />
                    Enregistrer en brouillon
                </label>

                <label class="flex items-center gap-2 text-sm mt-2">
                    <input type="radio" value="now" v-model="form.start_mode" />
                    Démarrer maintenant
                </label>

                <label class="flex items-center gap-2 text-sm mt-2 bg-black-400">
                    <input type="radio" value="later" v-model="form.start_mode" />
                    Planifier pour plus tard
                </label>

                <div v-if="form.start_mode === 'later'" class="mt-3">
                    <input
                        v-model="form.scheduled_at"
                        type="datetime-local"
                        class="w-full border rounded px-3 py-2 text-sm text-black bg-white"
                    />
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    @click="submitForm"
                    :disabled="loading"
                    class="px-4 py-2 bg-purple-800 text-white rounded hover:bg-purple-700 disabled:opacity-50"
                >
                    {{ loading ? 'Création...' : 'Créer' }}
                </button>
                <button
                    @click="emit('cancel')"
                    class="px-4 py-2 bg-gray-500 rounded hover:bg-gray-600"
                >
                    Annuler
                </button>
            </div>

        </div>
    </div>
</template>