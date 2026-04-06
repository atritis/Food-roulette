<template>
  <div class="container home">
    <SearchBar @search="onSearch" />
    <SearchFilters @update="onFiltersUpdate" />

    <div class="home__actions">
      <button class="btn btn--primary" @click="router.push({ name: 'recipe-create' })">
        + Neues Rezept
      </button>
    </div>

    <div v-if="store.isLoading || store.isSearching" class="home__loading">
      Laden...
    </div>

    <RecipeGrid
      v-else
      :recipes="displayedRecipes"
    />

    <p v-if="!store.isLoading && !store.isSearching && hasSearched && displayedRecipes.length === 0" class="home__empty">
      Keine Rezepte gefunden.
    </p>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useRecipeStore } from '../stores/recipeStore'
import SearchBar from '../components/SearchBar.vue'
import SearchFilters from '../components/SearchFilters.vue'
import RecipeGrid from '../components/RecipeGrid.vue'

const store = useRecipeStore()
const router = useRouter()

const hasSearched = ref(false)
const currentQuery = ref('')
const filters = ref({
  inTitle: true,
  inIngredients: true,
  inTags: true,
})

const displayedRecipes = computed(() => {
  if (hasSearched.value) {
    return store.searchResults
  }
  return store.randomRecipes
})

onMounted(() => {
  store.fetchRandom(2)
})

function onSearch(query) {
  currentQuery.value = query
  if (query.length >= 3) {
    hasSearched.value = true
    store.search(query, filters.value)
  } else {
    hasSearched.value = false
    store.searchResults = []
  }
}

function onFiltersUpdate(newFilters) {
  filters.value = newFilters
  if (currentQuery.value.length >= 3) {
    store.search(currentQuery.value, filters.value)
  }
}

</script>

<style lang="scss">
.home {
  &__actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1.5rem;
  }

  &__loading {
    text-align: center;
    padding: 3rem 0;
    color: var(--color-text-muted);
  }

  &__empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--color-text-muted);
  }
}
</style>
