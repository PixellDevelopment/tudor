<template>
  <div class="tudor-filter-group">
    <button
      class="tudor-filter-group__toggle"
      @click="expanded = !expanded"
      :aria-expanded="expanded"
    >
      <span class="tudor-filter-group__label">{{ label }}</span>
      <span class="tudor-filter-group__count" v-if="activeCount">{{ activeCount }}</span>
      <svg
        class="tudor-filter-group__chevron"
        :class="{ 'tudor-filter-group__chevron--open': expanded }"
        width="16" height="16" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2"
      >
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>

    <Transition name="filter-expand">
      <div v-if="expanded" class="tudor-filter-group__options">
        <label
          v-for="range in visibleRanges"
          :key="range.key"
          class="tudor-filter-option"
          :class="{ 'tudor-filter-option--active': isActive(range.key) }"
        >
          <input
            type="checkbox"
            class="tudor-filter-option__checkbox"
            :checked="isActive(range.key)"
            @change="$emit('toggle', range.key)"
          />
          <span class="tudor-filter-option__box" />
          <span class="tudor-filter-option__text">{{ range.label }}</span>
        </label>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const ALL_RANGES = [
  { key: '2000-3999',  min: 2000,  max: 3999,      label: '2.000 € - 3.999 €' },
  { key: '4000-5999',  min: 4000,  max: 5999,      label: '4.000 € - 5.999 €' },
  { key: '6000-7999',  min: 6000,  max: 7999,      label: '6.000 € - 7.999 €' },
  { key: '8000-9999',  min: 8000,  max: 9999,      label: '8.000 € - 9.999 €' },
  { key: '10000+',     min: 10000, max: Infinity,  label: '10.000 € E Oltre'  },
]

const props = defineProps({
  label:      { type: String, default: 'Prezzo' },
  priceRange: { type: Object, default: () => ({ min: 0, max: Infinity }) },
  active:     { type: Array,  default: () => [] },
})

defineEmits(['toggle'])

const expanded = ref(false)

const visibleRanges = computed(() =>
  ALL_RANGES.filter(r => r.min <= props.priceRange.max && r.max >= props.priceRange.min)
)

function isActive(key) {
  return props.active.includes(key)
}

const activeCount = computed(() =>
  props.active.filter(k => visibleRanges.value.some(r => r.key === k)).length
)
</script>