<script setup>
import { ref, watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import { useHashRoute } from "./composables/useHashRoute";
import PollTable from './components/PollTable.vue';
import CreatePoll from "./components/CreatePoll.vue";
import EditPoll from './components/EditPoll.vue';

const selectedPoll = ref(null);

function editPoll(poll) {
    selectedPoll.value = poll;
    navigateTo('#/polls/edit');
}

const props = defineProps({
    loginUrl: { type: String, default: null },
});

const { currentComponent, navigateTo } = useHashRoute([
    { hash: '#/', component: PollTable },
    { hash: '#/polls/config', component: CreatePoll },
    { hash: '#/polls/edit', component: EditPoll },
]);

const { fetchApiToRef } = useFetchApi();

const {
    data: getResult,
    error: getError,
    fetchNow,
} = fetchApiToRef({ url: "polls/" });

const { data: postResult, error: postError } = fetchApiToRef({
    url: "/foo",
    data: { id: 1 },
});

function handleError(err) {
    if (!err) return;
    if (err?.status === 401) {
        window.location.href = props.loginUrl;
    } else {
        console.error(err);
    }
}

watch(getError, (err) => handleError(err));
watch(postError, handleError);

usePolling(fetchNow);

</script>

<template>
    <main class="min-h-screen p-6">
        <h1 class="mb-4 text-xl font-semibold">Dashboard intégré</h1>

        <PollTable
            v-if="currentComponent === PollTable"
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

    </main>
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
