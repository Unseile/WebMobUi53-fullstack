<script setup>
import { watch } from "vue";
import { useFetchApi } from "./composables/useFetchApi";
import { usePolling } from "./composables/usePolling";
import PollTable from './components/PollTable.vue';

const props = defineProps({
    loginUrl: { type: String, default: null },
});

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

    <PollTable :polls="getResult || []" />

    <!-- <section class="mt-6">
      <h2>GET /api/v1/polls</h2>
      <pre v-if="getResult">{{ getResult }}</pre>
      <p v-else>Chargement...</p>
    </section> -->
  </main>
<!-- 
    <section>
        <h2>GET /api/v1/polls</h2>
        <pre v-if="getResult">{{ getResult }}</pre>
        <p v-else>Chargement...</p>
    </section>

    <section>
        <h2>POST /api/v1/foo</h2>
        <pre v-if="postResult">{{ postResult }}</pre>
        <p v-else>Chargement...</p>
    </section> -->
</template>

<style scoped>
section {
    margin-top: 1rem;
}
</style>
