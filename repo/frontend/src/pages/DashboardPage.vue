<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '@/components/layout/AppShell.vue'
import Badge from '@/components/ui/Badge.vue'
import Card from '@/components/ui/Card.vue'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user || { username: 'Guest', role: 'rider' })

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}
</script>

<template>
  <AppShell :user="user" @logout="handleLogout">
    <div class="dashboard-header">
      <h1>Welcome back, {{ user.username }}</h1>
      <Badge tone="success">{{ user.role }}</Badge>
    </div>

    <p class="helper-text">Your role-based dashboard shell is ready for trip, vehicle, and commerce modules.</p>

    <section class="stats-grid">
      <Card>
        <h3>Trips</h3>
        <p class="helper-text">No data yet. Metrics will appear once trip workflows are enabled.</p>
      </Card>

      <Card>
        <h3>Inventory</h3>
        <p class="helper-text">No data yet. Product modules will attach stock snapshots here.</p>
      </Card>

      <Card>
        <h3>Notifications</h3>
        <p class="helper-text">No data yet. Notification center integration is queued in next prompts.</p>
      </Card>
    </section>
  </AppShell>
</template>

<style scoped>
.dashboard-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  margin-bottom: var(--space-3);
}

h1 {
  margin: 0;
  font-size: clamp(1.4rem, 3vw, 2rem);
}

.stats-grid {
  margin-top: var(--space-6);
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--space-4);
}

h3 {
  margin-top: 0;
}

@media (max-width: 980px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
