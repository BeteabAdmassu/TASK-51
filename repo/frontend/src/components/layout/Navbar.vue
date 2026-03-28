<script setup>
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'

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

const toggleTheme = () => {
  const current = document.documentElement.dataset.theme || 'dark'
  const next = current === 'dark' ? 'light' : 'dark'
  document.documentElement.dataset.theme = next
  localStorage.setItem('roadlink_theme', next)
}
</script>

<template>
  <header class="navbar glass-card">
    <div class="navbar__left">
      <strong>RoadLink</strong>
      <Badge tone="info">{{ role }}</Badge>
    </div>

    <div class="navbar__right">
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
