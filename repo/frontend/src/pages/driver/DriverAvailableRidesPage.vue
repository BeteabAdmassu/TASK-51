<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '@/components/layout/AppShell.vue'
import AvailableRideCard from '@/components/driver/AvailableRideCard.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user || { username: 'Guest', role: 'driver' })
const rides = ref([])
const isLoading = ref(false)
const actionLoadingId = ref(null)
let pollTimer = null

const fetchAvailableRides = async () => {
  isLoading.value = true

  try {
    const response = await api.get('/driver/available-rides', {
      params: {
        per_page: 20,
      },
    })
    rides.value = response.data.data || []
  } finally {
    isLoading.value = false
  }
}

const acceptRide = async (rideId) => {
  if (!window.confirm('Accept this ride request?')) {
    return
  }

  actionLoadingId.value = rideId

  try {
    await api.patch(`/ride-orders/${rideId}/transition`, {
      action: 'accept',
    })
    await fetchAvailableRides()
  } finally {
    actionLoadingId.value = null
  }
}

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}

onMounted(async () => {
  await fetchAvailableRides()
  pollTimer = setInterval(fetchAvailableRides, 15000)
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
      <div>
        <h1>Available Rides</h1>
        <p class="helper-text">Matching requests near your current schedule.</p>
      </div>
    </section>

    <section class="list">
      <p v-if="isLoading" class="helper-text">Refreshing available rides...</p>

      <div v-else-if="rides.length === 0" class="empty-state glass-card">
        <p class="icon">🚘</p>
        <h3>No rides available right now. Check back soon!</h3>
      </div>

      <AvailableRideCard
        v-for="ride in rides"
        :key="ride.id"
        :ride="ride"
        :loading="actionLoadingId === ride.id"
        @accept="acceptRide"
      />
    </section>
  </AppShell>
</template>

<style scoped>
h1 {
  margin: 0;
}

.list {
  display: grid;
  gap: var(--space-3);
}

.empty-state {
  text-align: center;
  padding: var(--space-8) var(--space-4);
}

.icon {
  font-size: 2rem;
  margin: 0 0 var(--space-2);
}

h3 {
  margin: 0;
}
</style>
