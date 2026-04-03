<template>
  <div class="recipe-grid">
    <RecipeCard
      v-for="recipe in recipes"
      :key="recipe.id"
      :recipe="recipe"
    />
  </div>
</template>

<script setup>
import RecipeCard from './RecipeCard.vue'

defineProps({
  recipes: {
    type: Array,
    required: true,
  },
})
</script>

<style lang="scss">
@use '@/assets/styles/mixins';

.recipe-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;

  @include mixins.tablet {
    grid-template-columns: repeat(2, 1fr);
  }

  @include mixins.desktop {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
