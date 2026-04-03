<template>
  <router-link
    :to="{ name: 'recipe', params: { id: recipe.id } }"
    class="recipe-card"
  >
    <div class="recipe-card__image-wrap">
      <img
        v-if="recipe.images && recipe.images.length"
        :src="`/api/uploads/${recipe.images[0].image_path}`"
        :alt="recipe.title"
        class="recipe-card__image"
        loading="lazy"
      />
      <div v-else class="recipe-card__placeholder">
        <span>Kein Bild</span>
      </div>
    </div>
    <div class="recipe-card__body">
      <h3 class="recipe-card__title">{{ recipe.title }}</h3>
      <div class="recipe-card__meta">
        <span v-if="recipe.prep_time" class="recipe-card__time">
          &#9201; {{ recipe.prep_time }} Min. Vorbereitung
        </span>
        <span v-if="recipe.cook_time" class="recipe-card__time">
          &#127859; {{ recipe.cook_time }} Min. Kochen
        </span>
      </div>
      <div v-if="recipe.tags && recipe.tags.length" class="recipe-card__tags">
        <span
          v-for="tag in recipe.tags"
          :key="tag.id"
          class="tag-pill"
          :style="{ backgroundColor: tag.color, color: getContrastColor(tag.color) }"
        >
          {{ tag.name }}
        </span>
      </div>
    </div>
  </router-link>
</template>

<script setup>
defineProps({
  recipe: {
    type: Object,
    required: true,
  },
})

function getContrastColor(hex) {
  if (!hex) return '#fff'
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return luminance > 0.5 ? '#1a1a2e' : '#ffffff'
}
</script>

<style lang="scss">
.recipe-card {
  display: block;
  background: var(--color-card-bg);
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  text-decoration: none;
  color: var(--color-text);
  transition: box-shadow var(--transition-normal), transform var(--transition-normal);

  &:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }

  &__image-wrap {
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: var(--color-bg-secondary);
  }

  &__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-text-muted);
    font-size: 0.875rem;
  }

  &__body {
    padding: 1rem;
  }

  &__title {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    line-height: 1.3;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.8125rem;
    color: var(--color-text-secondary);
    margin-bottom: 0.75rem;
  }

  &__time {
    display: flex;
    align-items: center;
    gap: 0.25rem;
  }

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
  }
}

.tag-pill {
  display: inline-block;
  padding: 0.125rem 0.5rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 500;
  line-height: 1.5;
}
</style>
