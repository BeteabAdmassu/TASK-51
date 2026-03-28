<script setup>
import { ref } from 'vue'
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

const emit = defineEmits(['start', 'complete', 'flag-exception'])

const showExceptionModal = ref(false)
const exceptionReason = ref('')
const validationError = ref('')

const openExceptionModal = () => {
  showExceptionModal.value = true
  validationError.value = ''
}

const submitException = () => {
  if (!exceptionReason.value.trim()) {
    validationError.value = 'Reason is required.'
    return
  }

  emit('flag-exception', exceptionReason.value.trim())
  showExceptionModal.value = false
  exceptionReason.value = ''
  validationError.value = ''
}
</script>

<template>
  <div class="actions">
    <Button
      v-if="ride.status === 'accepted'"
      :loading="loading"
      @click="emit('start')"
    >
      Start Trip
    </Button>

    <Button
      v-if="ride.status === 'in_progress'"
      :loading="loading"
      @click="emit('complete')"
    >
      Complete Trip
    </Button>

    <Button
      v-if="['accepted', 'in_progress'].includes(ride.status)"
      variant="ghost"
      @click="openExceptionModal"
    >
      Flag Exception
    </Button>
  </div>

  <Teleport to="body">
    <div v-if="showExceptionModal" class="modal-backdrop" @click.self="showExceptionModal = false">
      <section class="modal glass-card">
        <h3>Flag Exception</h3>
        <textarea v-model="exceptionReason" placeholder="Describe the issue..." maxlength="1000" />
        <p v-if="validationError" class="error-text">{{ validationError }}</p>
        <div class="modal-actions">
          <Button variant="ghost" @click="showExceptionModal = false">Cancel</Button>
          <Button @click="submitException">Submit</Button>
        </div>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
}

.actions :deep(button) {
  width: auto;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(8, 11, 21, 0.72);
  display: grid;
  place-items: center;
  z-index: 95;
  padding: var(--space-4);
}

.modal {
  width: min(520px, 100%);
  padding: var(--space-5);
}

h3 {
  margin-top: 0;
}

textarea {
  width: 100%;
  min-height: 120px;
  resize: vertical;
  padding: 10px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background: rgba(20, 26, 47, 0.45);
  color: var(--color-text);
}

.error-text {
  color: var(--color-error);
}

.modal-actions {
  margin-top: var(--space-3);
  display: flex;
  justify-content: flex-end;
  gap: var(--space-2);
}
</style>
