<template>
  <div class="carousel" v-if="images.length">
    <div class="carousel__viewport">
      <img
        :src="`${baseUrl}api/uploads/${images[currentIndex].image_path}`"
        :alt="`Bild ${currentIndex + 1}`"
        class="carousel__image"
      />
      <button
        v-if="images.length > 1"
        class="carousel__btn carousel__btn--prev"
        @click="prev"
      >
        &#8249;
      </button>
      <button
        v-if="images.length > 1"
        class="carousel__btn carousel__btn--next"
        @click="next"
      >
        &#8250;
      </button>
    </div>
    <div v-if="images.length > 1" class="carousel__dots">
      <button
        v-for="(_, i) in images"
        :key="i"
        class="carousel__dot"
        :class="{ 'carousel__dot--active': i === currentIndex }"
        @click="currentIndex = i"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const baseUrl = import.meta.env.BASE_URL

const props = defineProps({
  images: { type: Array, default: () => [] },
})

const currentIndex = ref(0)

function prev() {
  currentIndex.value = (currentIndex.value - 1 + props.images.length) % props.images.length
}

function next() {
  currentIndex.value = (currentIndex.value + 1) % props.images.length
}
</script>

<style lang="scss">
.carousel {
  margin-bottom: 1.5rem;

  &__viewport {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    aspect-ratio: 16 / 10;
    background: var(--color-bg-secondary);
  }

  &__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity var(--transition-normal);
  }

  &__btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 2.5rem;
    height: 2.5rem;
    border: none;
    background: rgba(0, 0, 0, 0.4);
    color: #fff;
    border-radius: 50%;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background var(--transition-fast);

    &:hover {
      background: rgba(0, 0, 0, 0.6);
    }

    &--prev { left: 0.75rem; }
    &--next { right: 0.75rem; }
  }

  &__dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 0.75rem;
  }

  &__dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    border: none;
    background: var(--color-border);
    cursor: pointer;
    transition: background var(--transition-fast);

    &--active {
      background: var(--color-primary);
    }
  }
}
</style>
