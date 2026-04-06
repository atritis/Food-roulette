<template>
  <div class="tag-input">
    <label class="tag-input__label">Tags</label>

    <div class="tag-input__tags" v-if="modelValue.length">
      <span
        v-for="(tag, index) in modelValue"
        :key="index"
        class="tag-pill tag-pill--removable"
        :style="{ backgroundColor: tag.color, color: getContrastColor(tag.color) }"
      >
        {{ tag.name }}
        <button
          type="button"
          class="tag-pill__remove"
          @click="removeTag(index)"
          :style="{ color: getContrastColor(tag.color) }"
        >
          &times;
        </button>
      </span>
    </div>

    <div class="tag-input__row">
      <div class="tag-input__autocomplete-wrap">
        <input
          type="text"
          class="form-input"
          placeholder="Tag eingeben..."
          v-model="tagName"
          @input="onInput"
          @focus="showSuggestions = true"
          @keydown.enter.prevent="addTag"
        />
        <ul
          v-if="showSuggestions && filteredSuggestions.length"
          class="tag-input__suggestions"
        >
          <li
            v-for="suggestion in filteredSuggestions"
            :key="suggestion.id"
            @mousedown.prevent="selectSuggestion(suggestion)"
            class="tag-input__suggestion"
          >
            <span
              class="tag-pill"
              :style="{ backgroundColor: suggestion.color, color: getContrastColor(suggestion.color) }"
            >
              {{ suggestion.name }}
            </span>
          </li>
        </ul>
      </div>
      <input
        type="color"
        v-model="tagColor"
        class="tag-input__color"
        title="Farbe wählen"
      />
      <button type="button" class="btn btn--primary" @click="addTag">
        Hinzufügen
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useApi } from '../composables/useApi'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])

const api = useApi()
const tagName = ref('')
const tagColor = ref('#1F4D2B')
const allTags = ref([])
const showSuggestions = ref(false)

onMounted(async () => {
  try {
    allTags.value = await api.get('/tags')
  } catch {
    // ignore
  }
})

const filteredSuggestions = computed(() => {
  if (!tagName.value) return []
  const q = tagName.value.toLowerCase()
  return allTags.value.filter(
    t => t.name.toLowerCase().includes(q) &&
      !props.modelValue.some(v => v.name.toLowerCase() === t.name.toLowerCase())
  )
})

function onInput() {
  showSuggestions.value = true
}

function selectSuggestion(suggestion) {
  tagName.value = suggestion.name
  tagColor.value = suggestion.color
  showSuggestions.value = false
  addTag()
}

function addTag() {
  const name = tagName.value.trim()
  if (!name) return
  if (props.modelValue.some(t => t.name.toLowerCase() === name.toLowerCase())) return

  emit('update:modelValue', [...props.modelValue, { name, color: tagColor.value }])
  tagName.value = ''
  tagColor.value = '#1F4D2B'
  showSuggestions.value = false
}

function removeTag(index) {
  const updated = [...props.modelValue]
  updated.splice(index, 1)
  emit('update:modelValue', updated)
}

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
.tag-input {
  &__label {
    display: block;
    margin-bottom: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    margin-bottom: 0.75rem;
  }

  &__row {
    display: flex;
    gap: 0.5rem;
    align-items: flex-start;
  }

  &__autocomplete-wrap {
    flex: 1;
    position: relative;
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

    &:hover {
      background: var(--color-primary-light);
    }
  }

  &__color {
    width: 2.5rem;
    height: 2.5rem;
    border: 1px solid var(--color-input-border);
    border-radius: var(--radius-sm);
    padding: 2px;
    cursor: pointer;
    background: none;
  }
}

.tag-pill--removable {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding-right: 0.25rem;
}

.tag-pill__remove {
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1rem;
  line-height: 1;
  padding: 0 0.125rem;
  opacity: 0.7;

  &:hover {
    opacity: 1;
  }
}
</style>
