<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import { useHashRoute } from "./composables/useHashRoute";
import PollTable from './components/PollTable.vue';
import CreatePoll from "./components/CreatePoll.vue";
import EditPoll from './components/EditPoll.vue';
import ShowPoll from './components/ShowPoll.vue';

const selectedPoll = ref(null);
const showTokenPath = ref(false);

function editPoll(poll) {
    selectedPoll.value = poll;
    navigateTo('#/polls/edit');
}

function syncPathFromUrl() {
    const path = window.location.pathname;
    showTokenPath.value = /^\/polls\/[0-9a-fA-F-]{36}$/.test(path);
}

const props = defineProps({
    loginUrl: { type: String, default: null },
});

const { currentComponent, navigateTo } = useHashRoute([
    { hash: '#/', component: PollTable },
    { hash: '#/polls/config', component: CreatePoll },
    { hash: '#/polls/edit', component: EditPoll },
    { hash: '#/polls/show', component: ShowPoll }
]);

const { fetchApiToRef } = useFetchApi();

const {
    data: getResult,
    error: getError,
    fetchNow,
} = fetchApiToRef({ url: "polls/" });

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
        window.location.href = props.loginUrl;
    } else {
        console.error(err);
    }
}

watch(getError, (err) => handleError(err));

usePolling(fetchNow);

onMounted(() => {
    syncPathFromUrl();
    window.addEventListener('popstate', syncPathFromUrl);
});

onBeforeUnmount(() => {
    window.removeEventListener('popstate', syncPathFromUrl);
});

</script>

<template>
    <main class="min-h-screen p-6">
        <h1 class="mb-4 text-xl font-semibold">Dashboard intégré</h1>

        <ShowPoll
            v-if="showTokenPath"
            :poll="selectedPoll"
            @cancel="window.location.href = '/polls/dashboard-integrated'"
            @updated="navigateTo('#/'); fetchNow()"
        />

        <PollTable
            v-else-if="currentComponent === PollTable"
            :polls="getResult || []"
            @create-poll="navigateTo('#/polls/config')"
            @edit-poll="editPoll"
            @poll-deleted="fetchNow"
        />

        <CreatePoll
            v-else-if="currentComponent === CreatePoll"
            @cancel="navigateTo('#/')"
            @created="navigateTo('#/'); fetchNow()"
        />

        <EditPoll
            v-else-if="currentComponent === EditPoll"
            :poll="selectedPoll"
            @cancel="navigateTo('#/')"
            @updated="navigateTo('#/'); fetchNow()"
        />

        <ShowPoll
            v-else-if="currentComponent === ShowPoll"
            :poll="selectedPoll"
            @cancel="navigateTo('#/')"
            @updated="navigateTo('#/'); fetchNow()"
        />

    </main>
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
