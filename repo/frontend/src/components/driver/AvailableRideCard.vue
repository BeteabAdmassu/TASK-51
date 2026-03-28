<script setup>
import { computed } from 'vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  ride: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['accept'])

const notesExpanded = defineModel('notesExpanded', { type: Boolean, default: false })

const prettyWindow = computed(() => {
  const start = new Date(props.ride.time_window_start)
  const end = new Date(props.ride.time_window_end)

  return `${start.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })} - ${end.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })}`
})

const timeUntilAutoCancel = computed(() => {
  const expiresAt = new Date(props.ride.created_at).getTime() + 10 * 60 * 1000
  const diff = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000))
  const minutes = Math.floor(diff / 60)
  const seconds = diff % 60

  return `${minutes}:${String(seconds).padStart(2, '0')}`
})

const displayedNotes = computed(() => {
  if (!props.ride.notes) {
    return ''
  }

  if (notesExpanded.value || props.ride.notes.length <= 120) {
    return props.ride.notes
  }

  return `${props.ride.notes.slice(0, 120)}...`
})
</script>

<template>
  <article class="available-card glass-card">
    <header class="available-card__header">
      <h3>🛣️ {{ ride.origin_address }} → {{ ride.destination_address }}</h3>
      <span class="available-card__count">👤 x {{ ride.rider_count }}</span>
    </header>

    <p class="available-card__window">{{ prettyWindow }}</p>

    <p v-if="ride.notes" class="available-card__notes">
      {{ displayedNotes }}
      <button type="button" class="toggle-notes" @click="notesExpanded = !notesExpanded">
        {{ notesExpanded ? 'less' : 'more' }}
      </button>
    </p>

    <div class="available-card__footer">
      <span class="countdown">Auto-cancel in {{ timeUntilAutoCancel }}</span>
      <Button :loading="loading" @click="emit('accept', ride.id)">Accept</Button>
    </div>
  </article>
</template>

<style scoped>
.available-card {
  padding: var(--space-4);
  display: grid;
  gap: var(--space-2);
}

.available-card__header {
  display: flex;
  justify-content: space-between;
  gap: var(--space-3);
}

.available-card__header h3 {
  margin: 0;
  font-size: 1rem;
}

.available-card__count,
.available-card__window,
.available-card__notes,
.countdown {
  color: var(--color-text-muted);
  font-size: 0.9rem;
}

.available-card__window,
.available-card__notes {
  margin: 0;
}

.available-card__footer {
  display: flex;
  justify-content: space-between;
  gap: var(--space-3);
  align-items: center;
}

.available-card__footer :deep(button) {
  width: auto;
}

.toggle-notes {
  border: none;
  background: none;
  color: #8ca0ff;
  cursor: pointer;
}

@media (max-width: 760px) {
  .available-card__header,
  .available-card__footer {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
