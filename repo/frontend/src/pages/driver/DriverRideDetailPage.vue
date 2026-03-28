<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppShell from '@/components/layout/AppShell.vue'
import DriverRideActions from '@/components/driver/DriverRideActions.vue'
import OrderTimeline from '@/components/rides/OrderTimeline.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/authStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user || { username: 'Guest', role: 'driver' })
const ride = ref(null)
const isLoading = ref(false)
const actionLoading = ref(false)
const errorMessage = ref('')
let pollTimer = null

const fetchRide = async () => {
  isLoading.value = true

  try {
    const response = await api.get(`/driver/my-rides/${route.params.id}`)
    ride.value = response.data.order
    errorMessage.value = ''
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Unable to load ride.'
  } finally {
    isLoading.value = false
  }
}

const applyAction = async (action, reason = undefined) => {
  if (!ride.value) {
    return
  }

  actionLoading.value = true

  try {
    await api.patch(`/ride-orders/${ride.value.id}/transition`, {
      action,
      reason,
    })

    await fetchRide()
  } finally {
    actionLoading.value = false
  }
}

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}

onMounted(async () => {
  await fetchRide()
  pollTimer = setInterval(fetchRide, 15000)
})

onBeforeUnmount(() => {
  if (pollTimer) {
    clearInterval(pollTimer)
  }
})
</script>

<template>
  <AppShell :user="user" @logout="handleLogout">
    <p v-if="isLoading" class="helper-text">Loading ride...</p>
    <p v-else-if="errorMessage" class="error-text">{{ errorMessage }}</p>

    <section v-else-if="ride" class="detail-page">
      <header class="detail-header glass-card">
        <h1>{{ ride.origin_address }} → {{ ride.destination_address }}</h1>
        <p class="helper-text">👤 x {{ ride.rider_count }} · {{ new Date(ride.time_window_start).toLocaleString() }} - {{ new Date(ride.time_window_end).toLocaleTimeString() }}</p>
        <p class="notes" v-if="ride.notes">Rider notes: {{ ride.notes }}</p>
        <span class="status-pill">{{ ride.status.replace('_', ' ') }}</span>
      </header>

      <section class="glass-card action-panel">
        <h2>Actions</h2>
        <DriverRideActions
          :ride="ride"
          :loading="actionLoading"
          @start="applyAction('start')"
          @complete="applyAction('complete')"
          @flag-exception="(reason) => applyAction('flag_exception', reason)"
        />
      </section>

      <section class="glass-card timeline-panel">
        <h2>Audit Timeline</h2>
        <OrderTimeline :logs="ride.audit_logs || []" :current-status="ride.status" />
      </section>
    </section>
  </AppShell>
</template>

<style scoped>
.detail-page {
  display: grid;
  gap: var(--space-4);
}

.detail-header,
.action-panel,
.timeline-panel {
  padding: var(--space-5);
}

h1,
h2 {
  margin-top: 0;
}

.status-pill {
  display: inline-flex;
  border-radius: 999px;
  border: 1px solid var(--color-border);
  background: rgba(67, 97, 238, 0.22);
  padding: 6px 12px;
  text-transform: capitalize;
}

.notes {
  margin: var(--space-2) 0 0;
}

.error-text {
  color: var(--color-error);
}
</style>
