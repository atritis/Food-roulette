<template>
  <Teleport to="body">
    <div class="overlay" @mousedown.self="$emit('close')">
      <div class="overlay__content">
        <button class="overlay__close btn btn--ghost btn--icon" @click="$emit('close')">
          &times;
        </button>
        <slot />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'

defineEmits(['close'])

onMounted(() => {
  document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style lang="scss">
.overlay {
  position: fixed;
  inset: 0;
  background: var(--color-bg-overlay);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;

  &__content {
    background: var(--color-bg-secondary);
    border-radius: var(--radius-lg);
    padding: 2rem;
    width: 100%;
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: var(--shadow-lg);
  }

  &__close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    font-size: 1.5rem;
    width: 2rem;
    height: 2rem;
    z-index: 1;
  }
}
</style>
