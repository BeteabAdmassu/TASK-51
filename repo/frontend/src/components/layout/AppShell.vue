<script setup>
import Navbar from './Navbar.vue'
import Sidebar from './Sidebar.vue'

defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['logout'])
</script>

<template>
  <div class="shell page-container">
    <div class="shell__sidebar">
      <Sidebar :role="user.role" />
    </div>

    <div class="shell__content">
      <Navbar
        :username="user.username"
        :role="user.role"
        @logout="emit('logout')"
      />

      <main class="shell__main glass-card">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.shell {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 290px minmax(0, 1fr);
  gap: var(--space-4);
  padding: var(--space-4);
}

.shell__content {
  display: grid;
  gap: var(--space-4);
  align-content: start;
}

.shell__main {
  padding: var(--space-6);
  min-height: calc(100vh - 132px);
}

@media (max-width: 980px) {
  .shell {
    grid-template-columns: 1fr;
  }

  .shell__sidebar {
    order: 2;
  }
}
</style>
