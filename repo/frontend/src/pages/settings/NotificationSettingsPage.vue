<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import AppShell from '@/components/layout/AppShell.vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const router = useRouter()

const user = computed(() => authStore.user || { username: 'Guest', role: 'rider' })
const subscriptions = ref([])
const entityType = ref('ride_order')
const entityId = ref('')
const error = ref('')

const loadSubscriptions = async () => {
  const response = await api.get('/notification-subscriptions')
  subscriptions.value = response.data.data || []
}

const addSubscription = async () => {
  error.value = ''

  try {
    await api.post('/notification-subscriptions', {
      entity_type: entityType.value,
      entity_id: Number(entityId.value),
    })
    entityId.value = ''
    await loadSubscriptions()
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not add subscription.'
  }
}

const removeSubscription = async (subscriptionId) => {
  await api.delete(`/notification-subscriptions/${subscriptionId}`)
  await loadSubscriptions()
}

const handleLogout = async () => {
  await authStore.logout()
  await router.push('/login')
}

onMounted(loadSubscriptions)
</script>

<template>
  <AppShell :user="user" @logout="handleLogout">
    <section class="header">
      <h1>Notification Preferences</h1>
      <p class="helper-text">Opt in to high-priority alerts for specific rides or products.</p>
    </section>

    <section class="glass-card block">
      <h2>Add Subscription</h2>
      <form class="form" @submit.prevent="addSubscription">
        <label>
          Entity Type
          <select v-model="entityType">
            <option value="ride_order">Ride Order</option>
            <option value="product">Product</option>
          </select>
        </label>

        <label>
          Entity ID
          <input v-model="entityId" type="number" min="1" required>
        </label>

        <button class="add-btn" type="submit">Save Subscription</button>
      </form>
      <p v-if="error" class="error">{{ error }}</p>
    </section>

    <section class="glass-card block">
      <h2>Current Subscriptions</h2>

      <p v-if="!subscriptions.length" class="helper-text">No subscriptions yet.</p>

      <div v-else class="list">
        <article v-for="item in subscriptions" :key="item.id" class="item">
          <div>
            <strong>{{ item.entity_type }}</strong>
            <p>ID: {{ item.entity_id }}</p>
          </div>
          <button class="remove-btn" type="button" @click="removeSubscription(item.id)">Remove</button>
        </article>
      </div>
    </section>
  </AppShell>
</template>

<style scoped>
h1,
h2,
p {
  margin: 0;
}

.header {
  margin-bottom: var(--space-4);
}

.block {
  padding: var(--space-4);
  margin-bottom: var(--space-3);
  display: grid;
  gap: var(--space-2);
}

.form {
  display: grid;
  gap: var(--space-2);
}

label {
  display: grid;
  gap: 6px;
  color: var(--color-text-muted);
}

input,
select {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 9px 10px;
  color: var(--color-text);
  background: rgba(20, 26, 47, 0.45);
}

.add-btn,
.remove-btn {
  border: none;
  border-radius: 999px;
  padding: 8px 12px;
  cursor: pointer;
}

.add-btn {
  justify-self: start;
  color: #fff;
  background: linear-gradient(120deg, var(--color-accent), #5f7cff);
}

.remove-btn {
  color: var(--color-error);
  background: transparent;
  border: 1px solid rgba(239, 71, 111, 0.4);
}

.list {
  display: grid;
  gap: var(--space-2);
}

.item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-sm);
  padding: 10px;
}

.error {
  color: var(--color-error);
}
</style>
