<script setup>
import { watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import { useHashRoute } from "./composables/useHashRoute";
import PollTable from './components/PollTable.vue';
import CreatePoll from "./components/CreatePoll.vue";

const props = defineProps({
    loginUrl: { type: String, default: null },
});

const { currentComponent, navigateTo } = useHashRoute([
    { hash: '#/', component: PollTable },
    { hash: '#/polls/config', component: CreatePoll },
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
        <component
        :is="currentComponent"
        :polls="getResult || []"
        @create-poll="navigateTo('#/polls/config')"
        @cancel="navigateTo('#/')"
        @created="navigateTo('#/'); fetchNow()"
        @poll-deleted="fetchNow"
        />
  </main>
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
