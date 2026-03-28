<script setup>
import { onBeforeUnmount, ref, watch } from 'vue'

const emit = defineEmits(['close'])

const props = defineProps({
  message: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'info',
  },
})

const timeoutId = ref(null)

watch(
  () => props.message,
  (value) => {
    if (!value) {
      return
    }

    clearTimeout(timeoutId.value)
    timeoutId.value = setTimeout(() => emit('close'), 3500)
  },
)

onBeforeUnmount(() => clearTimeout(timeoutId.value))
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="message" class="toast" :class="`toast--${type}`">
        {{ message }}
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.toast {
  position: fixed;
  right: var(--space-5);
  bottom: var(--space-5);
  z-index: 90;
  border-radius: var(--radius-md);
  padding: 10px 16px;
  font-weight: 500;
  box-shadow: var(--shadow-md);
}

.toast--info {
  color: #dce5ff;
  background: rgba(29, 47, 122, 0.88);
}

.toast--error {
  color: #ffdbe3;
  background: rgba(129, 29, 56, 0.9);
}
</style>
