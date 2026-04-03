<template>
  <div class="container detail" v-if="recipe">
    <!-- Action buttons -->
    <div class="detail__actions">
      <template v-if="!isEditing">
        <button class="btn btn--ghost" @click="startEdit">Bearbeiten</button>
        <button class="btn btn--danger" @click="showDeleteConfirm = true">Löschen</button>
      </template>
      <template v-else>
        <button class="btn btn--ghost" @click="cancelEdit">Abbrechen</button>
        <button class="btn btn--primary" @click="saveEdit" :disabled="isSaving">
          {{ isSaving ? 'Speichern...' : 'Speichern' }}
        </button>
      </template>
    </div>

    <!-- Image carousel -->
    <ImageCarousel
      v-if="!isEditing"
      :images="recipe.images"
    />

    <!-- Edit mode: image management -->
    <div v-if="isEditing" class="detail__images-edit">
      <label class="detail__label">Bilder</label>
      <div class="detail__existing-images" v-if="recipe.images.length">
        <div
          v-for="img in recipe.images"
          :key="img.id"
          class="detail__existing-image"
        >
          <img :src="`/api/uploads/${img.image_path}`" :alt="recipe.title" />
          <button class="detail__image-remove" @click="removeExistingImage(img.id)">&times;</button>
        </div>
      </div>
      <ImageUploader @update="onNewImages" />
    </div>

    <!-- Title -->
    <h1 v-if="!isEditing" class="detail__title">{{ recipe.title }}</h1>
    <div v-else class="form-group">
      <label>Titel</label>
      <input type="text" class="form-input" v-model="editData.title" />
    </div>

    <!-- Tags -->
    <div v-if="!isEditing && recipe.tags.length" class="detail__tags">
      <span
        v-for="tag in recipe.tags"
        :key="tag.id"
        class="tag-pill"
        :style="{ backgroundColor: tag.color, color: getContrastColor(tag.color) }"
      >
        {{ tag.name }}
      </span>
    </div>
    <TagInput v-if="isEditing" v-model="editData.tags" />

    <!-- Meta info -->
    <div class="detail__meta">
      <template v-if="!isEditing">
        <span v-if="recipe.prep_time">&#9201; {{ recipe.prep_time }} Min. Vorbereitung</span>
        <span v-if="recipe.cook_time">&#127859; {{ recipe.cook_time }} Min. Kochen</span>
        <span>Erstellt: {{ formatDate(recipe.created_at) }}</span>
      </template>
      <template v-else>
        <div class="detail__meta-edit">
          <div class="form-group">
            <label>Zubereitungszeit (Min.)</label>
            <input type="number" class="form-input" v-model.number="editData.prep_time" min="0" />
          </div>
          <div class="form-group">
            <label>Kochzeit (Min.)</label>
            <input type="number" class="form-input" v-model.number="editData.cook_time" min="0" />
          </div>
          <div class="form-group">
            <label>Portionen</label>
            <input type="number" class="form-input" v-model.number="editData.servings" min="1" />
          </div>
        </div>
      </template>
    </div>

    <!-- Portion adjuster (display mode only) -->
    <PortionAdjuster
      v-if="!isEditing"
      v-model="desiredServings"
    />

    <!-- Ingredients -->
    <section class="detail__section" v-if="!isEditing">
      <h2 class="detail__section-title">Zutaten</h2>
      <ul class="detail__ingredients" v-if="adjustedIngredients.length">
        <li v-for="(ing, i) in adjustedIngredients" :key="i" class="detail__ingredient">
          <span v-if="ing.adjustedQuantity" class="detail__ingredient-qty">
            {{ ing.adjustedQuantity }} {{ ing.unit }}
          </span>
          <span>{{ ing.name }}</span>
        </li>
      </ul>
      <p v-else class="detail__empty">Keine Zutaten hinterlegt.</p>
    </section>
    <IngredientInput v-if="isEditing" v-model="editData.ingredients" />

    <!-- Instructions -->
    <section class="detail__section">
      <h2 class="detail__section-title">Zubereitung</h2>
      <div v-if="!isEditing" class="detail__instructions" v-html="formatInstructions(recipe.instructions)"></div>
      <div v-else class="form-group">
        <textarea class="form-input" v-model="editData.instructions" rows="8"></textarea>
      </div>
    </section>

    <!-- Error message -->
    <p v-if="error" class="detail__error">{{ error }}</p>

    <!-- Delete confirmation -->
    <ConfirmDialog
      v-if="showDeleteConfirm"
      title="Rezept löschen"
      message="Möchtest du dieses Rezept wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden."
      confirm-text="Endgültig löschen"
      @confirm="onDelete"
      @cancel="showDeleteConfirm = false"
    />
  </div>

  <div v-else-if="store.isLoading" class="container">
    <p class="detail__loading">Laden...</p>
  </div>

  <div v-else class="container">
    <p class="detail__not-found">Rezept nicht gefunden.</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRecipeStore } from '../stores/recipeStore'
import { usePortionAdjust } from '../composables/usePortionAdjust'
import ImageCarousel from '../components/ImageCarousel.vue'
import ImageUploader from '../components/ImageUploader.vue'
import PortionAdjuster from '../components/PortionAdjuster.vue'
import TagInput from '../components/TagInput.vue'
import IngredientInput from '../components/IngredientInput.vue'
import ConfirmDialog from '../components/ConfirmDialog.vue'

const route = useRoute()
const router = useRouter()
const store = useRecipeStore()

const recipe = computed(() => store.currentRecipe)
const isEditing = ref(false)
const isSaving = ref(false)
const showDeleteConfirm = ref(false)
const error = ref('')
const editData = ref({})
const newImageFiles = ref([])

// Portion adjustment
const desiredServings = ref(4)
const originalServings = computed(() => recipe.value?.servings || 4)
const ingredientsRef = computed(() => recipe.value?.ingredients || [])

const { adjustedIngredients } = usePortionAdjust(ingredientsRef, originalServings, desiredServings)

// Reset desired servings when recipe loads
watch(recipe, (r) => {
  if (r) {
    desiredServings.value = r.servings || 4
  }
})

onMounted(() => {
  store.fetchRecipe(Number(route.params.id))
})

function startEdit() {
  editData.value = JSON.parse(JSON.stringify(recipe.value))
  isEditing.value = true
}

function cancelEdit() {
  isEditing.value = false
  editData.value = {}
  newImageFiles.value = []
  error.value = ''
}

async function saveEdit() {
  isSaving.value = true
  error.value = ''

  try {
    const validIngredients = editData.value.ingredients.filter(i => i.name.trim())
    await store.updateRecipe(recipe.value.id, {
      title: editData.value.title,
      instructions: editData.value.instructions,
      prep_time: editData.value.prep_time,
      cook_time: editData.value.cook_time,
      servings: editData.value.servings,
      tags: editData.value.tags,
      ingredients: validIngredients,
    })

    if (newImageFiles.value.length > 0) {
      await store.uploadImages(recipe.value.id, newImageFiles.value)
    }

    // Re-fetch to get updated data
    await store.fetchRecipe(recipe.value.id)
    isEditing.value = false
    newImageFiles.value = []
  } catch (e) {
    error.value = e.message || 'Fehler beim Speichern.'
  } finally {
    isSaving.value = false
  }
}

async function removeExistingImage(imageId) {
  try {
    await store.deleteImage(imageId)
    await store.fetchRecipe(recipe.value.id)
  } catch (e) {
    error.value = e.message
  }
}

function onNewImages(files) {
  newImageFiles.value = files
}

async function onDelete() {
  try {
    await store.deleteRecipe(recipe.value.id)
    router.push({ name: 'home' })
  } catch (e) {
    error.value = e.message
    showDeleteConfirm.value = false
  }
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

function formatInstructions(text) {
  if (!text) return ''
  return text.replace(/\n/g, '<br>')
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
.detail {
  max-width: 800px;

  &__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }

  &__title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
    line-height: 1.2;
  }

  &__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    margin-bottom: 1rem;
  }

  &__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-bottom: 1.5rem;
  }

  &__meta-edit {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    width: 100%;
  }

  &__section {
    margin-bottom: 2rem;
  }

  &__section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--color-border);
  }

  &__ingredients {
    list-style: none;
  }

  &__ingredient {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--color-border);
    font-size: 0.9375rem;

    &:last-child {
      border-bottom: none;
    }
  }

  &__ingredient-qty {
    font-weight: 600;
    margin-right: 0.5rem;
  }

  &__instructions {
    line-height: 1.7;
    font-size: 0.9375rem;
  }

  &__images-edit {
    margin-bottom: 1.5rem;
  }

  &__label {
    display: block;
    margin-bottom: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }

  &__existing-images {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }

  &__existing-image {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: var(--radius-sm);
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }

  &__image-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 1.5rem;
    height: 1.5rem;
    border: none;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border-radius: 50%;
    font-size: 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      background: rgba(239, 68, 68, 0.9);
    }
  }

  &__empty {
    color: var(--color-text-muted);
    font-style: italic;
  }

  &__error {
    color: var(--color-danger);
    font-size: 0.875rem;
    margin-top: 1rem;
  }

  &__loading,
  &__not-found {
    text-align: center;
    padding: 3rem 0;
    color: var(--color-text-muted);
  }
}
</style>
