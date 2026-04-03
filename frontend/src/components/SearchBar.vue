<template>
  <div class="search-bar">
    <input
      type="text"
      class="search-bar__input form-input"
      placeholder="Rezept suchen..."
      v-model="query"
      @input="onInput"
    />
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { useDebounce } from '../composables/useDebounce'

const emit = defineEmits(['search'])
const query = ref('')

const { debounced, cancel } = useDebounce((value) => {
  emit('search', value)
}, 300)

function onInput() {
  if (query.value.length >= 3) {
    debounced(query.value)
  } else {
    cancel()
    emit('search', '')
  }
}

onUnmounted(cancel)
</script>

<style lang="scss">
.search-bar {
  margin-bottom: 1rem;

  &__input {
    font-size: 1.125rem;
    padding: 0.75rem 1rem;
  }
}
</style>
