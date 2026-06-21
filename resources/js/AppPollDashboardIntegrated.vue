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

function showPoll(poll) {
    selectedPoll.value = poll;
    navigateTo('#/polls/show');
}

function syncPathFromUrl() {
    const path = window.location.pathname;
    showTokenPath.value = /^\/polls\/[0-9a-fA-F-]{36}$/.test(path);
}

const props = defineProps({
    loginUrl: { type: String, default: null },
    isAuthenticated: { type: Boolean, default: false },
    currentUserId: { type: Number, default: null },
    initialPolls: { type: Array, default: () => [] },
});

const { currentComponent, navigateTo } = useHashRoute([
    { hash: '#/', component: PollTable },
    { hash: '#/polls/config', component: CreatePoll },
    { hash: '#/polls/edit', component: EditPoll },
    { hash: '#/polls/show', component: ShowPoll }
]);

const { fetchApiToRef } = useFetchApi();

const isTokenPath = /^\/polls\/[0-9a-fA-F-]{36}$/.test(window.location.pathname);

const {
    data: getResult,
    error: getError,
    fetchNow,
} = fetchApiToRef({ 
    url: "polls/",
    immediate: false,
});

getResult.value = props.initialPolls; 

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
        if (!isTokenPath) window.location.href = props.loginUrl;
    } else {
        console.error(err);
    }
}

watch(getError, (err) => handleError(err));

if (!isTokenPath) usePolling(fetchNow);

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
            :is-authenticated="props.isAuthenticated"
            :login-url="props.loginUrl"
            :current-user-id="props.currentUserId"
            @cancel="window.location.href = '/polls/dashboard-integrated'"
            @updated="navigateTo('#/'); fetchNow()"
        />

        <template v-else>
            <PollTable
                v-if="currentComponent === PollTable"
                :polls="getResult || []"
                @create-poll="navigateTo('#/polls/config')"
                @edit-poll="editPoll"
                @show-poll="showPoll"
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
                :is-authenticated="props.isAuthenticated"
                :login-url="props.loginUrl"
                :current-user-id="props.currentUserId"
                @cancel="navigateTo('#/')"
                @updated="navigateTo('#/'); fetchNow()"
            />
        </template>

    </main>
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
