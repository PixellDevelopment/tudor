<template>
  <div class="tudor-filter-group">
    <button class="tudor-filter-group__toggle" @click="expanded = !expanded" :aria-expanded="expanded">
      <span class="tudor-filter-group__label">{{ label }}</span>
      <span class="tudor-filter-group__count" v-if="activeCount">{{ activeCount }}</span>
      <svg
        class="tudor-filter-group__chevron"
        :class="{ 'tudor-filter-group__chevron--open': expanded }"
        width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
      >
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </button>

    <Transition name="filter-expand">
      <div v-if="expanded" class="tudor-filter-group__options">
        <label
          v-for="item in items"
          :key="getValue(item)"
          class="tudor-filter-option"
          :class="{ 'tudor-filter-option--active': isActive(getValue(item)) }"
        >
          <input
            type="checkbox"
            class="tudor-filter-option__checkbox"
            :checked="isActive(getValue(item))"
            @change="$emit('toggle', getValue(item))"
          />
          <span class="tudor-filter-option__box" />
          <span class="tudor-filter-option__text">{{ getLabel(item) }}</span>
        </label>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  label: String,
  items: Array,
  active: Array,
  valueKey: { type: String, default: 'name' },
  labelKey: { type: String, default: null },
  labelMap: { type: Object, default: null },
})

defineEmits(['toggle'])

const expanded = ref(false)

function getValue(item) {
  return item[props.valueKey]
}

function getLabel(item) {
  const key = props.labelKey ? item[props.labelKey] : item['name'] || item[props.valueKey]
  if (props.labelMap && props.labelMap[key?.toLowerCase()]) {
    return props.labelMap[key.toLowerCase()]
  }
  return key
}

function isActive(val) {
  return props.active.includes(val)
}

const activeCount = computed(() => props.active.filter(v => props.items.some(i => getValue(i) === v)).length)
</script>
