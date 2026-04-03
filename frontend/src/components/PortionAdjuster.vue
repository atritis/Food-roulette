<template>
  <div class="portion-adjuster">
    <label class="portion-adjuster__label">Portionen</label>
    <div class="portion-adjuster__controls">
      <button
        type="button"
        class="btn btn--ghost btn--icon"
        @click="emit('update:modelValue', Math.max(1, modelValue - 1))"
        :disabled="modelValue <= 1"
      >
        &minus;
      </button>
      <span class="portion-adjuster__value">{{ modelValue }}</span>
      <button
        type="button"
        class="btn btn--ghost btn--icon"
        @click="emit('update:modelValue', modelValue + 1)"
      >
        +
      </button>
    </div>
  </div>
</template>

<script setup>
const { modelValue } = defineProps({
  modelValue: { type: Number, required: true },
})

const emit = defineEmits(['update:modelValue'])
</script>

<style lang="scss">
.portion-adjuster {
  display: flex;
  align-items: center;
  gap: 0.75rem;

  &__label {
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }

  &__controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  &__value {
    font-size: 1.125rem;
    font-weight: 600;
    min-width: 2rem;
    text-align: center;
  }
}
</style>
