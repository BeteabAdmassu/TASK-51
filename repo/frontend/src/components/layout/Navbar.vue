<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import NotificationPanel from '@/components/notifications/NotificationPanel.vue'
import api from '@/services/api'

const emit = defineEmits(['logout'])

const props = defineProps({
  username: {
    type: String,
    default: '',
  },
  role: {
    type: String,
    default: 'rider',
  },
})

const router = useRouter()
const unreadCount = ref(0)
const panelOpen = ref(false)
let pollTimer = null

const fetchUnreadCount = async () => {
  try {
    const response = await api.get('/notifications/unread-count')
    unreadCount.value = Number(response.data.unread_count || 0)
  } catch {
    unreadCount.value = 0
  }
}

const togglePanel = async () => {
  panelOpen.value = !panelOpen.value
  if (panelOpen.value) {
    await fetchUnreadCount()
  }
}

const openSettings = async () => {
  panelOpen.value = false
  await router.push('/settings/notifications')
}

const toggleTheme = () => {
  const current = document.documentElement.dataset.theme || 'dark'
  const next = current === 'dark' ? 'light' : 'dark'
  document.documentElement.dataset.theme = next
  localStorage.setItem('roadlink_theme', next)
}

onMounted(async () => {
  await fetchUnreadCount()
  pollTimer = setInterval(fetchUnreadCount, 30000)
})

onBeforeUnmount(() => {
  if (pollTimer) {
    clearInterval(pollTimer)
  }
})
</script>

<template>
  <header class="navbar glass-card">
    <div class="navbar__left">
      <strong>RoadLink</strong>
      <Badge tone="info">{{ role }}</Badge>
    </div>

    <div class="navbar__right">
      <div class="notification-wrap">
        <button class="notification-btn" type="button" @click="togglePanel">
          Bell
          <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
        </button>

        <div v-if="panelOpen" class="panel-popover">
          <NotificationPanel
            :open="panelOpen"
            @close="panelOpen = false"
            @count-updated="unreadCount = $event"
          />
          <button class="settings-link" type="button" @click="openSettings">Notification settings</button>
        </div>
      </div>

      <button class="theme-switch" type="button" @click="toggleTheme">
        Theme
      </button>

      <div class="profile">
        <span class="avatar">{{ (props.username || 'R').slice(0, 1).toUpperCase() }}</span>
        <span>{{ props.username }}</span>
      </div>

      <Button variant="ghost" @click="emit('logout')">
        Logout
      </Button>
    </div>
  </header>
</template>

<style scoped>
.navbar {
  padding: var(--space-4) var(--space-5);
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--space-4);
}

.navbar__left,
.navbar__right {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.theme-switch {
  border: 1px solid var(--color-border);
  color: var(--color-text);
  background: rgba(255, 255, 255, 0.04);
  border-radius: 999px;
  padding: 6px 10px;
  cursor: pointer;
}

.notification-wrap {
  position: relative;
}

.notification-btn {
  border: 1px solid var(--color-border);
  color: var(--color-text);
  background: rgba(255, 255, 255, 0.04);
  border-radius: 999px;
  padding: 6px 12px;
  cursor: pointer;
  position: relative;
}

.notification-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 18px;
  height: 18px;
  border-radius: 999px;
  background: rgba(239, 71, 111, 0.95);
  color: #fff;
  font-size: 0.68rem;
  display: inline-grid;
  place-items: center;
  padding: 0 4px;
}

.panel-popover {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  z-index: 50;
  display: grid;
  gap: 8px;
}

.settings-link {
  justify-self: end;
  border: none;
  background: transparent;
  color: var(--color-accent);
  cursor: pointer;
  font-size: 0.85rem;
}

.profile {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
}

.avatar {
  display: inline-grid;
  place-items: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(120deg, #4361ee, #6580ff);
  font-weight: 700;
  color: #f4f7ff;
}

@media (max-width: 900px) {
  .navbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .navbar__right {
    width: 100%;
    flex-wrap: wrap;
  }
}
</style>
