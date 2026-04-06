<template>
  <div class="container create">
    <RecipeForm @created="onCreated" @cancel="router.push({ name: 'home' })" />
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import RecipeForm from '../components/RecipeForm.vue'

const router = useRouter()

function onCreated(recipe) {
  router.push({ name: 'recipe', params: { id: recipe.id } })
}
</script>
