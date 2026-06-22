import { onMounted, onUnmounted } from 'vue';

/**
 * Polling composable
 *
 * @param {Function} fn - The function to call on each interval tick
 * @param {number} [interval=5000] - The interval in milliseconds
 */
export function usePolling(fn, interval = 5000, immediate = false) {
  let timer;

  onMounted(() => {
    if (immediate) fn();
    timer = setInterval(fn, interval);
  });

  onUnmounted(() => clearInterval(timer));
}
