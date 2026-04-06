<template>
  <div class="ingredient-input">
    <label class="ingredient-input__label">Zutaten</label>

    <div
      v-for="(item, index) in modelValue"
      :key="index"
      class="ingredient-input__row"
    >
      <div class="ingredient-input__name-wrap">
        <input
          type="text"
          class="form-input"
          placeholder="Zutat"
          :value="item.name"
          @input="updateField(index, 'name', $event.target.value)"
          @focus="activeSuggestionIndex = index; fetchSuggestions(item.name)"
          @blur="onBlur"
        />
        <ul
          v-if="activeSuggestionIndex === index && suggestions.length"
          class="ingredient-input__suggestions"
        >
          <li
            v-for="s in suggestions"
            :key="s.id"
            @mousedown.prevent="selectSuggestion(index, s)"
            class="ingredient-input__suggestion"
          >
            {{ s.name }}
          </li>
        </ul>
      </div>
      <input
        type="number"
        class="form-input ingredient-input__qty"
        placeholder="Menge"
        :value="item.quantity"
        @input="updateField(index, 'quantity', $event.target.value)"
        step="0.25"
        min="0"
      />
      <input
        type="text"
        class="form-input ingredient-input__unit"
        placeholder="Einheit"
        list="unit-suggestions"
        :value="item.unit"
        @input="updateField(index, 'unit', $event.target.value)"
      />
      <datalist id="unit-suggestions">
        <option value="g" />
        <option value="kg" />
        <option value="ml" />
        <option value="l" />
        <option value="TL" />
        <option value="EL" />
        <option value="Tasse" />
        <option value="Stück" />
        <option value="Prise" />
        <option value="Bund" />
        <option value="Scheibe" />
        <option value="n. B." />
      </datalist>
      <button
        type="button"
        class="btn btn--ghost btn--icon ingredient-input__remove"
        @click="removeIngredient(index)"
        title="Entfernen"
      >
        &times;
      </button>
    </div>

    <button type="button" class="btn btn--ghost" @click="addIngredient">
      + Zutat hinzufügen
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useApi } from '../composables/useApi'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

const api = useApi()
const suggestions = ref([])
const activeSuggestionIndex = ref(-1)
let fetchTimer = null

function updateField(index, field, value) {
  const updated = [...props.modelValue]
  updated[index] = { ...updated[index], [field]: value }
  emit('update:modelValue', updated)

  if (field === 'name') {
    fetchSuggestions(value)
  }
}

function addIngredient() {
  emit('update:modelValue', [...props.modelValue, { name: '', quantity: '', unit: '' }])
}

function removeIngredient(index) {
  const updated = [...props.modelValue]
  updated.splice(index, 1)
  emit('update:modelValue', updated)
}

async function fetchSuggestions(query) {
  clearTimeout(fetchTimer)
  if (!query || query.length < 1) {
    suggestions.value = []
    return
  }

  fetchTimer = setTimeout(async () => {
    try {
      suggestions.value = await api.get(`/ingredients?q=${encodeURIComponent(query)}`)
    } catch {
      suggestions.value = []
    }
  }, 150)
}

function selectSuggestion(index, suggestion) {
  updateField(index, 'name', suggestion.name)
  suggestions.value = []
  activeSuggestionIndex.value = -1
}

function onBlur() {
  setTimeout(() => {
    suggestions.value = []
    activeSuggestionIndex.value = -1
  }, 200)
}
</script>

<style lang="scss">
.ingredient-input {
  &__label {
    display: block;
    margin-bottom: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }

  &__row {
    display: grid;
    grid-template-columns: 1fr 5rem 6rem auto;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    align-items: flex-start;
  }

  &__name-wrap {
    position: relative;
    min-width: 0;
  }

  &__remove {
    font-size: 1.25rem;
  }

  &__suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    margin-top: 2px;
    max-height: 160px;
    overflow-y: auto;
    z-index: 10;
    list-style: none;
  }

  &__suggestion {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;

    &:hover {
      background: var(--color-primary-light);
    }
  }
}
</style>
