<script setup>
  import { ref, onMounted, onUnmounted } from 'vue';
  import { useFetchApi } from './composables/useFetchApi';

  const props = defineProps({
    loginUrl: { type: String, default: null },
  });

  const { fetchApi } = useFetchApi();

  const getResult = ref(null);
  const getError = ref(null);
  const postResult = ref(null);
  const postError = ref(null);

  function handleError(err) {
    if (err?.status === 401) window.location.href = props.loginUrl;
  }

  function fetchGet() {
    fetchApi({ url: '/foo' })
      .then(data => getResult.value = data)
      .catch(err => handleError(err));
  }

  function fetchPost() {
    fetchApi({ url: '/foo', data: { id: 1 } })
      .then(data => postResult.value = data)
      .catch(err => handleError(err));
  }

  onMounted(() => {
    fetchGet();
    fetchPost();
    var interval = setInterval(fetchGet, 5000);
  });

  onUnmounted(() => clearInterval(interval));
</script>

<template>
  <h1>Builder</h1>

  <section>
    <h2>GET /api/v1/foo</h2>
    <pre v-if="getResult">{{ getResult }}</pre>
    <pre v-else-if="getError">Erreur {{ getError.status }} : {{ getError.statusText }}</pre>
    <p v-else>Chargement...</p>
  </section>

  <section>
    <h2>POST /api/v1/foo</h2>
    <pre v-if="postResult">{{ postResult }}</pre>
    <pre v-else-if="postError">Erreur {{ postError.status }} : {{ postError.statusText }}</pre>
    <p v-else>Chargement...</p>
  </section>
</template>

<style scoped>
section {
  margin-top: 1rem;
}
</style>