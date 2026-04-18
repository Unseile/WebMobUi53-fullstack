import { computed, onBeforeUnmount, onMounted, shallowRef } from 'vue';

export function useHashRoute(routes) {
  const defaultRoute = routes[0];
  const currentRoute = shallowRef(defaultRoute);

  function syncRouteFromUrl() {
    const hash = window.location.hash;
    currentRoute.value = routes.find(route => route.hash === hash) ?? defaultRoute;
  }

  function navigateTo(hash) {
    window.history.pushState(null, '', hash);
    syncRouteFromUrl();
  }

  onMounted(() => {
    if (window.location.hash === '') {
      window.history.replaceState(null, '', defaultRoute.hash);
    }
    syncRouteFromUrl();
    window.addEventListener('popstate', syncRouteFromUrl);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('popstate', syncRouteFromUrl);
  });

  const currentComponent = computed(() => currentRoute.value.component);
  syncRouteFromUrl();

  return { currentComponent, currentRoute, navigateTo };
}
