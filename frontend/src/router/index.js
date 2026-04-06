import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/recipe/new',
    name: 'recipe-create',
    component: () => import('../views/RecipeCreateView.vue'),
  },
  {
    path: '/recipe/:id',
    name: 'recipe',
    component: () => import('../views/RecipeDetailView.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

export default router
