<template>
  <header class="header">
    <div class="container header__inner">
      <router-link to="/" class="header__logo">Food Roulette</router-link>
      <button
        class="header__theme-toggle btn btn--ghost btn--icon"
        @click="toggleTheme"
        :title="isDark ? 'Light Mode' : 'Dark Mode'"
      >
        <span v-if="isDark">&#9728;</span>
        <span v-else>&#9790;</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

onMounted(() => {
  const saved = localStorage.getItem('theme')
  if (saved) {
    isDark.value = saved === 'dark'
  } else {
    isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
  }
  applyTheme()
})

function toggleTheme() {
  isDark.value = !isDark.value
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
  applyTheme()
}

function applyTheme() {
  document.documentElement.setAttribute('data-theme', isDark.value ? 'dark' : '')
}
</script>

<style lang="scss">
.header {
  background-color: var(--color-bg-secondary);
  border-bottom: 1px solid var(--color-border);
  position: sticky;
  top: 0;
  z-index: 100;

  &__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 3.5rem;
  }

  &__logo {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--color-text);
    text-decoration: none;
    letter-spacing: -0.02em;

    &:hover {
      color: var(--color-primary);
    }
  }

  &__theme-toggle {
    font-size: 1.25rem;
    width: 2.25rem;
    height: 2.25rem;
  }
}
</style>
