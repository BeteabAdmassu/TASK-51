<script setup>
import { ref, watch } from 'vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  start: {
    type: String,
    default: '22:00',
  },
  end: {
    type: String,
    default: '07:00',
  },
})

const emit = defineEmits(['update:modelValue', 'save'])

const localStart = ref(props.start)
const localEnd = ref(props.end)

watch(() => props.start, (value) => {
  localStart.value = value
})

watch(() => props.end, (value) => {
  localEnd.value = value
})

const close = () => emit('update:modelValue', false)
const save = () => emit('save', { dnd_start: localStart.value, dnd_end: localEnd.value })
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-backdrop" @click.self="close">
      <section class="modal glass-card">
        <h3>DND Settings</h3>
        <p class="helper-text">Default: 10:00 PM - 7:00 AM (server time)</p>

        <label>
          <span class="helper-text">Start</span>
          <input v-model="localStart" type="time">
        </label>

        <label>
          <span class="helper-text">End</span>
          <input v-model="localEnd" type="time">
        </label>

        <div class="actions">
          <Button variant="ghost" @click="close">Cancel</Button>
          <Button @click="save">Save</Button>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(8, 11, 21, 0.72);
  z-index: 95;
  display: grid;
  place-items: center;
  padding: var(--space-4);
}

.modal {
  width: min(420px, 100%);
  padding: var(--space-5);
  display: grid;
  gap: var(--space-3);
}

h3,
p {
  margin: 0;
}

input {
  width: 100%;
  margin-top: var(--space-1);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  background: rgba(20, 26, 47, 0.45);
  color: var(--color-text);
  padding: 8px 10px;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2);
}

.actions :deep(button) {
  width: auto;
}
</style>
