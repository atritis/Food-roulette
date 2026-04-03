import { ref } from 'vue'

export function useDebounce(fn, delay = 300) {
  let timer = null
  const isPending = ref(false)

  function debounced(...args) {
    isPending.value = true
    clearTimeout(timer)
    timer = setTimeout(() => {
      isPending.value = false
      fn(...args)
    }, delay)
  }

  function cancel() {
    clearTimeout(timer)
    isPending.value = false
  }

  return { debounced, cancel, isPending }
}
