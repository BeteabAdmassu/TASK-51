<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '@/components/layout/AppShell.vue'
import DriverRideActions from '@/components/driver/DriverRideActions.vue'
import OrderTimeline from '@/components/rides/OrderTimeline.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user || { username: 'Guest', role: 'driver' })
const rides = ref([])
const isLoading = ref(false)
const actionLoadingId = ref(null)
const showHistory = ref(false)
let pollTimer = null

const activeRides = computed(() => rides.value.filter((ride) => ['accepted', 'in_progress'].includes(ride.status)))
const historyRides = computed(() => rides.value.filter((ride) => !['accepted', 'in_progress'].includes(ride.status)))

const noShowRemaining = (ride) => {
  if (ride.status !== 'accepted' || !ride.accepted_at) {
    return null
  }

  const elapsed = Math.floor((Date.now() - new Date(ride.accepted_at).getTime()) / 1000)
  const remaining = 300 - elapsed

  if (remaining > 120) {
    return null
  }

  const minutes = Math.max(0, Math.floor(remaining / 60))
  const seconds = Math.max(0, remaining % 60)
  return `${minutes}:${String(seconds).padStart(2, '0')}`
}

const fetchMyRides = async () => {
  isLoading.value = true

  try {
    const response = await api.get('/driver/my-rides', {
      params: {
        per_page: 40,
      },
    })
    rides.value = response.data.data || []
  } finally {
    isLoading.value = false
  }
}

const applyAction = async (rideId, action, reason = undefined) => {
  actionLoadingId.value = rideId

  try {
    await api.patch(`/ride-orders/${rideId}/transition`, {
      action,
      reason,
    })

    await fetchMyRides()
  } finally {
    actionLoadingId.value = null
  }
}

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}

onMounted(async () => {
  await fetchMyRides()
  pollTimer = setInterval(fetchMyRides, 15000)
})

onBeforeUnmount(() => {
  if (pollTimer) {
    clearInterval(pollTimer)
  }
})
</script>

<template>
  <AppShell :user="user" @logout="handleLogout">
    <section class="header">
      <h1>My Rides</h1>
      <p class="helper-text">Manage active rides and review trip history.</p>
    </section>

    <p v-if="isLoading" class="helper-text">Loading your rides...</p>

    <section v-else class="stack">
      <article v-for="ride in activeRides" :key="ride.id" class="ride-card glass-card">
        <header class="ride-card__header">
          <div>
            <h3>{{ ride.origin_address }} → {{ ride.destination_address }}</h3>
            <p class="helper-text">👤 x {{ ride.rider_count }} · {{ new Date(ride.time_window_start).toLocaleString() }}</p>
          </div>
          <span class="status">{{ ride.status.replace('_', ' ') }}</span>
        </header>

        <p v-if="ride.notes" class="notes">{{ ride.notes }}</p>

        <div v-if="noShowRemaining(ride)" class="warning">
          Please start this trip soon - it will be reassigned in {{ noShowRemaining(ride) }}.
        </div>

        <DriverRideActions
          :ride="ride"
          :loading="actionLoadingId === ride.id"
          @start="applyAction(ride.id, 'start')"
          @complete="applyAction(ride.id, 'complete')"
          @flag-exception="(reason) => applyAction(ride.id, 'flag_exception', reason)"
        />

        <OrderTimeline :logs="ride.audit_logs || []" :current-status="ride.status" />

        <button class="detail-link" type="button" @click="router.push(`/driver/my-rides/${ride.id}`)">View Full Detail</button>
        <button class="detail-link" type="button" @click="router.push(`/driver/my-rides/${ride.id}/chat`)">Open Chat</button>
      </article>

      <section class="history-section glass-card">
        <button class="history-toggle" type="button" @click="showHistory = !showHistory">
          {{ showHistory ? 'Hide' : 'Show' }} History ({{ historyRides.length }})
        </button>

        <div v-if="showHistory" class="history-list">
          <article v-for="ride in historyRides" :key="ride.id" class="history-item">
            <p>{{ ride.origin_address }} → {{ ride.destination_address }}</p>
            <span>{{ ride.status.replace('_', ' ') }}</span>
          </article>
        </div>
      </section>
    </section>
  </AppShell>
</template>

<style scoped>
h1 {
  margin: 0;
}

.stack {
  display: grid;
  gap: var(--space-4);
}

.ride-card {
  padding: var(--space-5);
  display: grid;
  gap: var(--space-3);
}

.ride-card__header {
  display: flex;
  justify-content: space-between;
  gap: var(--space-2);
}

h3 {
  margin: 0;
}

.status {
  border-radius: 999px;
  border: 1px solid var(--color-border);
  padding: 6px 10px;
  text-transform: capitalize;
  background: rgba(67, 97, 238, 0.2);
  height: fit-content;
}

.notes {
  margin: 0;
}

.warning {
  border: 1px solid rgba(255, 209, 102, 0.45);
  background: rgba(255, 209, 102, 0.12);
  color: #ffe0a1;
  border-radius: var(--radius-md);
  padding: 10px;
}

.detail-link,
.history-toggle {
  border: none;
  background: none;
  color: #8ca0ff;
  text-align: left;
  cursor: pointer;
  padding: 0;
}

.history-section {
  padding: var(--space-4);
}

.history-list {
  margin-top: var(--space-3);
  display: grid;
  gap: var(--space-2);
}

.history-item {
  display: flex;
  justify-content: space-between;
  color: var(--color-text-muted);
  border-bottom: 1px solid rgba(151, 164, 208, 0.14);
  padding-bottom: var(--space-2);
}

.history-item p {
  margin: 0;
}

@media (max-width: 760px) {
  .ride-card__header,
  .history-item {
    flex-direction: column;
  }
}
</style>
