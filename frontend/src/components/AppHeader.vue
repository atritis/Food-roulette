<template>
  <header class="header">
    <div class="container header__inner">
      <router-link to="/" class="header__logo">
        <img src="../assets/images/logo.png" alt="Food Roulette" class="header__logo-img" />
      </router-link>
    </div>
  </header>
</template>

<script setup>
</script>

<style lang="scss">
.header {
  background-color: var(--color-primary);
  position: sticky;
  top: 0;
  z-index: 100;

  &__inner {
    display: flex;
    align-items: center;
    height: 4rem;
  }

  &__logo {
    display: flex;
    align-items: center;
    text-decoration: none;
  }

  &__logo-img {
    height: 2.75rem;
    width: auto;
  }
}
</style>
