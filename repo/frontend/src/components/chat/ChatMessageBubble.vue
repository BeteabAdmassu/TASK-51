<script setup>
import { computed } from 'vue'

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
})

const isSystem = computed(() => props.message.type === 'system_notice')
const isOwn = computed(() => props.message.sender_id === props.currentUserId)
</script>

<template>
  <div v-if="isSystem" class="notice">
    <span>ℹ️</span>
    <p>{{ message.content }}</p>
  </div>

  <div v-else class="row" :class="{ own: isOwn }">
    <div class="bubble" :class="{ own: isOwn }">
      <p class="sender">{{ isOwn ? 'You' : (message.sender?.username || 'User') }}</p>
      <p class="content">{{ message.content }}</p>
      <p class="time">{{ new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</p>
    </div>
  </div>
</template>

<style scoped>
.row {
  display: flex;
}

.row.own {
  justify-content: flex-end;
}

.bubble {
  max-width: min(70%, 520px);
  background: rgba(125, 137, 173, 0.2);
  border-radius: var(--radius-md);
  padding: 10px 12px;
}

.bubble.own {
  background: rgba(67, 97, 238, 0.34);
}

.sender,
.time {
  margin: 0;
  color: var(--color-text-muted);
  font-size: 0.76rem;
}

.content {
  margin: 6px 0;
  white-space: pre-wrap;
}

.notice {
  text-align: center;
  color: var(--color-text-muted);
  font-size: 0.85rem;
  display: flex;
  justify-content: center;
  gap: 6px;
}

.notice p {
  margin: 0;
}
</style>
