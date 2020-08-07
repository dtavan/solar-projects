<template>
  <div class="app">
    <navbar />

    <main class="container">
      <router-view />
    </main>
  </div>
</template>

<script>
import Navbar from './Navbar.vue';

export default {
  components: {
    Navbar,
  },
}
</script>
