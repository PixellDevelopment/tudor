<template>
  <div class="tudor-collection" :class="{ 'tudor-collection--mobile': isMobile }">

    <!-- Toolbar: filtri + ordinamento -->
    <div class="tudor-toolbar">
      <div class="tudor-toolbar__left">
        <!-- Pulsante "Tutti i filtri" -->
        <button
          class="tudor-toolbar__filter-btn"
          :class="{ 'tudor-toolbar__filter-btn--active': activeFilterCount > 0 }"
          @click="filtersOpen = true"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/>
            <line x1="14" y1="18" x2="21" y2="18"/>
          </svg>
          {{ t.allFilters }}
          <span v-if="activeFilterCount > 0" class="tudor-toolbar__badge">{{ activeFilterCount }}</span>
        </button>

        <!-- Cancella filtri (visibile quando ci sono filtri attivi) -->
        <Transition name="fade">
          <button
            v-if="activeFilterCount > 0"
            class="tudor-toolbar__clear-btn"
            @click="clearAllFilters"
          >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            {{ t.clearFilters }}
          </button>
        </Transition>

        <!-- Contatore prodotti -->
        <span class="tudor-toolbar__count">
          {{ totalCount }} {{ totalCount === 1 ? t.product : t.products }}
        </span>
      </div>

      <div class="tudor-toolbar__right">
        <SortDropdown v-model="sortKey" :t="t" />
      </div>
    </div>

    <!-- Mobile quick filters (caliber pills) -->
    <div class="tudor-quick-filters" v-if="isMobile && filters?.calibers?.length">
      <button
        v-for="cal in filters.calibers.filter(c => c.visible)"
        :key="cal.name"
        class="tudor-quick-filter"
        :class="{ 'tudor-quick-filter--active': activeFilters.calibers.includes(cal.name) }"
        @click="toggleFilter('calibers', cal.name)"
      >
        {{ cal.name }}
      </button>
    </div>

    <!-- Griglia prodotti -->
    <div v-if="loading && !allProducts.length" class="tudor-grid tudor-grid--skeleton">
      <div v-for="n in 12" :key="n" class="tudor-card tudor-card--skeleton">
        <div class="tudor-card__image-wrap"><div class="tudor-card__skeleton" /></div>
        <div class="tudor-card__body">
          <div class="tudor-skeleton-line tudor-skeleton-line--short" />
          <div class="tudor-skeleton-line" />
          <div class="tudor-skeleton-line tudor-skeleton-line--price" />
        </div>
      </div>
    </div>

    <TransitionGroup
      v-else
      name="product-list"
      tag="div"
      class="tudor-grid"
    >
      <ProductCard
        v-for="product in visibleProducts"
        :key="product.prod_id"
        :product="product"
      />
    </TransitionGroup>

    <!-- No results -->
    <div v-if="!loading && totalCount === 0" class="tudor-no-results">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><line x1="8" y1="11" x2="14" y2="11"/>
      </svg>
      <p>{{ t.noProducts }}</p>
    </div>

    <!-- Scopri di più -->
    <div v-if="hasMore" class="tudor-load-more">
      <button class="tudor-load-more__btn" @click="loadMore" :disabled="loading">
        <span v-if="!loading">{{ t.discoverMore }}</span>
        <span v-else class="tudor-load-more__spinner" />
      </button>
      <p class="tudor-load-more__info">
        {{ visibleProducts.length }} / {{ totalCount }}
      </p>
    </div>

    <!-- Filter Panel Drawer -->
    <FilterPanel
      :open="filtersOpen"
      :filters="filters"
      :activeFilters="activeFilters"
      :t="t"
      @close="filtersOpen = false"
      @toggle="(group, val) => toggleFilter(group, val)"
      @clear="clearAllFilters"
    />

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import ProductCard from './ProductCard.vue'
import FilterPanel from './FilterPanel.vue'
import SortDropdown from './SortDropdown.vue'
import { useProducts } from '../composables/useProducts'
import { useTranslation } from '../composables/useTranslation'

const props = defineProps({
  brandId: { type: String, default: '525' },
  locale: { type: String, default: 'it' },
  apiBase: { type: String, default: '' },
  ajaxUrl: { type: String, default: '' },
  ajaxNonce: { type: String, default: '' },
})

const localeRef = computed(() => props.locale)
const { t } = useTranslation(localeRef)

const {
  allProducts, filters, loading, visibleProducts,
  hasMore, totalCount, activeFilterCount,
  activeFilters, sortKey,
  fetchFilters, fetchProducts,
  loadMore, clearAllFilters, toggleFilter,
} = useProducts(props.apiBase, props.brandId, localeRef)

const filtersOpen = ref(false)

// Responsive
const isMobile = ref(window.innerWidth < 768)
function onResize() { isMobile.value = window.innerWidth < 768 }
onMounted(() => window.addEventListener('resize', onResize))
onUnmounted(() => window.removeEventListener('resize', onResize))

onMounted(async () => {
  await Promise.all([fetchFilters(), fetchProducts()])
})
</script>
