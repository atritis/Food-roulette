<template>
  <form class="recipe-form" @submit.prevent="onSubmit">
    <h2 class="recipe-form__title">Neues Rezept</h2>

    <div class="form-group">
      <label>Titel</label>
      <input type="text" class="form-input" v-model="form.title" required />
    </div>

    <div class="recipe-form__row">
      <div class="form-group">
        <label>Zubereitungszeit (Min.)</label>
        <input type="number" class="form-input" v-model.number="form.prep_time" min="0" />
      </div>
      <div class="form-group">
        <label>Kochzeit (Min.)</label>
        <input type="number" class="form-input" v-model.number="form.cook_time" min="0" />
      </div>
      <div class="form-group">
        <label>Portionen</label>
        <input type="number" class="form-input" v-model.number="form.servings" min="1" />
      </div>
    </div>

    <div class="form-group">
      <label>Zubereitungsanleitung</label>
      <textarea class="form-input" v-model="form.instructions" rows="6"></textarea>
    </div>

    <TagInput v-model="form.tags" />

    <IngredientInput v-model="form.ingredients" />

    <ImageUploader @update="onImagesUpdate" />

    <div class="recipe-form__actions">
      <button type="button" class="btn btn--ghost" @click="$emit('cancel')">Abbrechen</button>
      <button type="submit" class="btn btn--primary" :disabled="isSaving">
        {{ isSaving ? 'Speichern...' : 'Rezept erstellen' }}
      </button>
    </div>

    <p v-if="error" class="recipe-form__error">{{ error }}</p>
  </form>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRecipeStore } from '../stores/recipeStore'
import TagInput from './TagInput.vue'
import IngredientInput from './IngredientInput.vue'
import ImageUploader from './ImageUploader.vue'

const emit = defineEmits(['created', 'cancel'])
const store = useRecipeStore()

const form = reactive({
  title: '',
  instructions: '',
  prep_time: 0,
  cook_time: 0,
  servings: 4,
  tags: [],
  ingredients: [{ name: '', quantity: '', unit: '' }],
})

const imageFiles = ref([])
const isSaving = ref(false)
const error = ref('')

function onImagesUpdate(files) {
  imageFiles.value = files
}

async function onSubmit() {
  if (!form.title.trim()) return

  isSaving.value = true
  error.value = ''

  try {
    const validIngredients = form.ingredients.filter(i => i.name.trim())
    const recipe = await store.createRecipe({
      ...form,
      ingredients: validIngredients,
    })

    if (imageFiles.value.length > 0) {
      await store.uploadImages(recipe.id, imageFiles.value)
    }

    // Re-fetch to get complete data with images
    await store.fetchRecipe(recipe.id)
    emit('created', store.currentRecipe)
  } catch (e) {
    error.value = e.message || 'Fehler beim Erstellen des Rezepts.'
  } finally {
    isSaving.value = false
  }
}
</script>

<style lang="scss">
.recipe-form {
  &__title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }

  &__row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
  }

  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--color-border);
  }

  &__error {
    color: var(--color-danger);
    font-size: 0.875rem;
    margin-top: 0.75rem;
  }
}
</style>
