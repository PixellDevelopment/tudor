<template>
  <div class="tudor-sort" ref="sortRef">
    <button class="tudor-sort__trigger" @click="open = !open" :aria-expanded="open">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="15" y2="18"/>
      </svg>
      <span>{{ currentLabel }}</span>
      <svg class="tudor-sort__chevron" :class="{ 'open': open }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>

    <Transition name="dropdown-fade">
      <div v-if="open" class="tudor-sort__dropdown">
        <button
          v-for="(label, key) in sortOptions"
          :key="key"
          class="tudor-sort__option"
          :class="{ 'tudor-sort__option--active': modelValue === key }"
          @click="select(key)"
        >
          {{ label }}
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: String,
  t: Object,
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)
const sortRef = ref(null)

const sortOptions = computed(() => props.t?.sortOptions || {})

const currentLabel = computed(() => {
  return sortOptions.value[props.modelValue] || props.t?.sortBy || 'Ordina'
})

function select(key) {
  emit('update:modelValue', key)
  open.value = false
}

function handleClickOutside(e) {
  if (sortRef.value && !sortRef.value.contains(e.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside))
</script>
