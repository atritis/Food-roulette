<template>
  <OverlayModal @close="$emit('cancel')">
    <div class="confirm-dialog">
      <h3 class="confirm-dialog__title">{{ title }}</h3>
      <p class="confirm-dialog__message">{{ message }}</p>
      <div class="confirm-dialog__actions">
        <button class="btn btn--ghost" @click="$emit('cancel')">Abbrechen</button>
        <button class="btn btn--danger" @click="$emit('confirm')">{{ confirmText }}</button>
      </div>
    </div>
  </OverlayModal>
</template>

<script setup>
import OverlayModal from './OverlayModal.vue'

defineProps({
  title: { type: String, default: 'Bestätigung' },
  message: { type: String, default: 'Bist du sicher?' },
  confirmText: { type: String, default: 'Löschen' },
})

defineEmits(['confirm', 'cancel'])
</script>

<style lang="scss">
.confirm-dialog {
  text-align: center;
  padding: 1rem 0;

  &__title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
  }

  &__message {
    color: var(--color-text-secondary);
    margin-bottom: 1.5rem;
  }

  &__actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
  }
}
</style>
