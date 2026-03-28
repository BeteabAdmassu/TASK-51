<script setup>
import { computed, ref } from 'vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['send'])
const content = ref('')

const count = computed(() => content.value.length)
const canSend = computed(() => !props.disabled && count.value > 0 && count.value <= 2000)

const submit = () => {
  if (!canSend.value) {
    return
  }

  emit('send', content.value)
  content.value = ''
}

const onKeyDown = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    submit()
  }
}
</script>

<template>
  <div class="composer">
    <textarea
      v-model="content"
      :disabled="disabled"
      maxlength="2000"
      rows="1"
      placeholder="Type a message..."
      @keydown="onKeyDown"
    />
    <div class="composer__footer">
      <span>{{ count }} / 2000</span>
      <Button :disabled="!canSend" @click="submit">Send</Button>
    </div>
  </div>
</template>

<style scoped>
.composer {
  border-top: 1px solid rgba(151, 164, 208, 0.2);
  padding-top: var(--space-3);
}

textarea {
  width: 100%;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: rgba(20, 26, 47, 0.45);
  color: var(--color-text);
  padding: 10px 12px;
  resize: none;
  min-height: 46px;
  max-height: 120px;
}

.composer__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: var(--space-2);
  gap: var(--space-2);
}

.composer__footer span {
  color: var(--color-text-muted);
  font-size: 0.82rem;
}

.composer__footer :deep(button) {
  width: auto;
}
</style>
