<template>
  <!-- Overlay -->
  <Transition name="overlay-fade">
    <div v-if="open" class="tudor-filters-overlay" @click="$emit('close')" />
  </Transition>

  <!-- Drawer -->
  <Transition name="drawer-slide">
    <div v-if="open" class="tudor-filters-drawer" role="dialog" aria-modal="true" :aria-label="t.allFilters">
      <div class="tudor-filters-drawer__header">
        <h2 class="tudor-filters-drawer__title">{{ t.allFilters }}</h2>
        <button class="tudor-filters-drawer__close" @click="$emit('close')" :aria-label="t.close">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>

      <div class="tudor-filters-drawer__body">
        <!-- Style -->
        <!-- <FilterGroup v-if="filters?.style?.length" :label="t.filters.style"
          :items="filters.style.filter(s => s.visible)" :active="activeFilters.style" value-key="name"
          :label-map="t.styleOptions" @toggle="(v) => $emit('toggle', 'style', v)" /> -->

        <!-- Collections -->
        <FilterGroup v-if="filters?.collections?.length" :label="t.filters.collections"
          :items="filters.collections.filter((s) => s.visible)" :active="activeFilters.collections" value-key="name"
          label-key="name" @toggle="(v) => $emit('toggle', 'collections', v)" />

        <!-- Dimensione -->
        <FilterGroup v-if="filters?.measures?.length" :label="t.filters.measures"
          :items="filters.measures.filter((s) => s.visible)" :active="activeFilters.measures" value-key="name"
          @toggle="(v) => $emit('toggle', 'measures', v)" />

        <!-- Calibro -->
        <!-- <FilterGroup v-if="filters?.calibers?.length" :label="t.filters.calibers"
          :items="filters.calibers.filter(s => s.visible)" :active="activeFilters.calibers" value-key="name"
          @toggle="(v) => $emit('toggle', 'calibers', v)" /> -->

        <!-- Materiale -->
        <FilterGroup v-if="filters?.materials?.length" :label="t.filters.materials"
          :items="filters.materials.filter((s) => s.visible)" :active="activeFilters.materials" value-key="name"
          @toggle="(v) => $emit('toggle', 'materials', v)" />

        <!-- Bracciale -->
        <!-- <FilterGroup v-if="filters?.bracelets?.length" :label="t.filters.bracelets"
          :items="filters.bracelets.filter(s => s.visible)" :active="activeFilters.bracelets" value-key="name"
          @toggle="(v) => $emit('toggle', 'bracelets', v)" /> -->

        <!-- Lunetta -->
        <!-- <FilterGroup v-if="filters?.lunettes?.length" :label="t.filters.lunettes"
          :items="filters.lunettes.filter(s => s.visible)" :active="activeFilters.lunettes" value-key="name"
          @toggle="(v) => $emit('toggle', 'lunettes', v)" /> -->

        <!-- Impermeabilità -->
        <!-- <FilterGroup v-if="filters?.impermeabilities?.length" :label="t.filters.impermeabilities"
          :items="filters.impermeabilities.filter(s => s.visible)" :active="activeFilters.impermeabilities"
          value-key="name" @toggle="(v) => $emit('toggle', 'impermeabilities', v)" /> -->

        <!-- Movimento -->
        <!-- <FilterGroup v-if="filters?.movements?.length" :label="t.filters.movements"
          :items="filters.movements.filter(s => s.visible)" :active="activeFilters.movements" value-key="name"
          @toggle="(v) => $emit('toggle', 'movements', v)" /> -->

        <!-- Forma -->
        <!-- <FilterGroup v-if="filters?.forms?.length" :label="t.filters.forms"
          :items="filters.forms.filter(s => s.visible)" :active="activeFilters.forms" value-key="name"
          @toggle="(v) => $emit('toggle', 'forms', v)" /> -->

        <!-- Fibbia -->
        <!-- <FilterGroup v-if="filters?.buckles?.length" :label="t.filters.buckles"
          :items="filters.buckles.filter(s => s.visible)" :active="activeFilters.buckles" value-key="name"
          @toggle="(v) => $emit('toggle', 'buckles', v)" /> -->

        <!-- Colori -->
        <FilterGroup v-if="filters?.colors?.length" :label="t.filters.colors"
          :items="filters.colors.filter((s) => s.visible)" :active="activeFilters.colors" value-key="name"
          @toggle="(v) => $emit('toggle', 'colors', v)" />

        <PriceFilterGroup v-if="filters?.price_range" :label="t.filters.price" :price-range="filters.price_range"
          :active="activeFilters.priceRanges" @toggle="(v) => $emit('toggle', 'priceRanges', v)" />

        <!-- Disponibilità -->
        <FilterGroup v-if="availabilityItems.length" :label="t.filters.availability" :items="availabilityItems"
          :active="activeFilters.availability" value-key="name" :label-map="t.availabilityOptions"
          @toggle="(v) => $emit('toggle', 'availability', v)" />
      </div>

      <div class="tudor-filters-drawer__footer">
        <button class="tudor-filters-drawer__clear" @click="$emit('clear')">
          {{ t.clearFilters }}
        </button>
        <button class="tudor-filters-drawer__apply" @click="$emit('close')">
          {{ t.apply }}
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from "vue";
import FilterGroup from "./FilterGroup.vue";
import PriceFilterGroup from "./PriceFilterGroup.vue";

const props = defineProps({
  open: Boolean,
  filters: Object,
  activeFilters: Object,
  t: Object,
});

defineEmits(["close", "toggle", "clear"]);

const availabilityItems = computed(() => [
  { name: "available", visible: true },
  { name: "unavailable", visible: true },
]);
</script>
