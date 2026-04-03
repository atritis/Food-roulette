import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useApi } from '../composables/useApi'

export const useRecipeStore = defineStore('recipe', () => {
  const randomRecipes = ref([])
  const searchResults = ref([])
  const currentRecipe = ref(null)
  const isSearching = ref(false)
  const isLoading = ref(false)

  const api = useApi()

  async function fetchRandom(count = 2) {
    isLoading.value = true
    try {
      randomRecipes.value = await api.get(`/recipes/random?count=${count}`)
    } finally {
      isLoading.value = false
    }
  }

  async function search(query, filters = {}) {
    if (query.length < 3) {
      searchResults.value = []
      return
    }

    isSearching.value = true
    const params = new URLSearchParams({
      q: query,
      in_title: filters.inTitle !== false ? '1' : '0',
      in_ingredients: filters.inIngredients !== false ? '1' : '0',
      in_tags: filters.inTags !== false ? '1' : '0',
    })

    try {
      searchResults.value = await api.get(`/recipes/search?${params}`)
    } finally {
      isSearching.value = false
    }
  }

  async function fetchRecipe(id) {
    isLoading.value = true
    try {
      currentRecipe.value = await api.get(`/recipes/${id}`)
    } finally {
      isLoading.value = false
    }
  }

  async function createRecipe(data) {
    return await api.post('/recipes', data)
  }

  async function updateRecipe(id, data) {
    const updated = await api.put(`/recipes/${id}`, data)
    currentRecipe.value = updated
    return updated
  }

  async function deleteRecipe(id) {
    await api.del(`/recipes/${id}`)
    currentRecipe.value = null
  }

  async function uploadImages(recipeId, files) {
    const formData = new FormData()
    files.forEach(file => formData.append('images[]', file))
    return await api.postForm(`/recipes/${recipeId}/images`, formData)
  }

  async function deleteImage(imageId) {
    await api.del(`/images/${imageId}`)
  }

  return {
    randomRecipes,
    searchResults,
    currentRecipe,
    isSearching,
    isLoading,
    fetchRandom,
    search,
    fetchRecipe,
    createRecipe,
    updateRecipe,
    deleteRecipe,
    uploadImages,
    deleteImage,
  }
})
