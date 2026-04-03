<template>
  <div class="search-filters">
    <label class="search-filters__item">
      <input type="checkbox" v-model="filters.inTitle" @change="onChange" />
      <span>Titel</span>
    </label>
    <label class="search-filters__item">
      <input type="checkbox" v-model="filters.inIngredients" @change="onChange" />
      <span>Zutaten</span>
    </label>
    <label class="search-filters__item">
      <input type="checkbox" v-model="filters.inTags" @change="onChange" />
      <span>Tags</span>
    </label>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['update'])

const filters = reactive({
  inTitle: true,
  inIngredients: true,
  inTags: true,
})

function onChange() {
  emit('update', { ...filters })
}
</script>

<style lang="scss">
.search-filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;

  &__item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    cursor: pointer;

    input[type="checkbox"] {
      accent-color: var(--color-primary);
    }
  }
}
</style>
