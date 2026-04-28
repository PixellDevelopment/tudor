import { ref, computed, watch } from 'vue'
import { useCache } from './useCache'

const PAGE_SIZE = 30

const PRICE_RANGES = {
  '2000-3999': { min: 2000, max: 3999 },
  '4000-5999': { min: 4000, max: 5999 },
  '6000-7999': { min: 6000, max: 7999 },
  '8000-9999': { min: 8000, max: 9999 },
  '10000+': { min: 10000, max: Infinity },
}

// ✅ MAPPE MULTILINGUA
const MATERIAL_MAP = {
  "Acciaio": { it: "Acciaio", en: "Steel" },
  "Acciaio e Oro Giallo": { it: "Acciaio e Oro Giallo", en: "Steel & Yellow Gold" },
  "Acciaio e Oro Rosa": { it: "Acciaio e Oro Rosa", en: "Steel & Rose Gold" },
  "Argento": { it: "Argento", en: "Silver" },
  "Bronzo": { it: "Bronzo", en: "Bronze" },
  "Fibra di Carbonio": { it: "Fibra di Carbonio", en: "Carbon Fiber" },
  "Titanio": { it: "Titanio", en: "Titanium" }
}

const SIZE_MAP = {
  "Piccolo": { it: "Piccolo", en: "Small" },
  "Medio": { it: "Medio", en: "Medium" },
  "Grande": { it: "Grande", en: "Large" }
}

// ✅ helper corretto
function translateValue(map, value, locale) {
  return map[value]?.[locale] || value
}

export function useProducts(apiBase, brandId, locale) {
  const { get, set, buildKey } = useCache()

  const allProducts = ref([])
  const filters = ref(null)
  const loading = ref(false)
  const filtersLoading = ref(false)
  const error = ref(null)
  const page = ref(1)

  const activeFilters = ref({
    style: [], collections: [], measures: [], calibers: [],
    materials: [], bracelets: [], lunettes: [], impermeabilities: [],
    movements: [], forms: [], buckles: [],
    availability: [],
    priceRanges: [],
    colors: [],
  })

  const sortKey = ref('featured')

  function getLocale() {
    return (typeof locale === 'object' && locale !== null ? locale.value : locale) || 'it'
  }

  // =========================
  // FILTER + SORT
  // =========================
  const sortedFiltered = computed(() => {
    let list = [...allProducts.value]
    const af = activeFilters.value

    if (af.collections.length) {
      list = list.filter(p => af.collections.includes(p.collection))
    }

    if (af.availability.length && af.availability.length < 2) {
      const wantAvailable = af.availability.includes('available')
      list = list.filter(p => p.available === wantAvailable)
    }

    const strFilters = [
      ['measures', 'size'],
      ['materials', 'material'],
      ['colors', 'color'],
    ]

    for (const [fk, pk] of strFilters) {
      if (af[fk].length) {
        list = list.filter(p => af[fk].includes(p[pk]))
      }
    }

    if (af.priceRanges.length) {
      list = list.filter(p => {
        const price = parseFloat(p.price)
        return af.priceRanges.some(key => {
          const r = PRICE_RANGES[key]
          return r && price >= r.min && price <= r.max
        })
      })
    }

    switch (sortKey.value) {
      case 'alphaAZ':
        list.sort((a, b) => a.model_name.localeCompare(b.model_name))
        break
      case 'alphaZA':
        list.sort((a, b) => b.model_name.localeCompare(a.model_name))
        break
      case 'priceAsc':
        list.sort((a, b) => parseFloat(a.price) - parseFloat(b.price))
        break
      case 'priceDesc':
        list.sort((a, b) => parseFloat(b.price) - parseFloat(a.price))
        break
    }

    return list
  })

  const visibleProducts = computed(() =>
    sortedFiltered.value.slice(0, page.value * PAGE_SIZE)
  )

  const hasMore = computed(() =>
    visibleProducts.value.length < sortedFiltered.value.length
  )

  const totalCount = computed(() =>
    sortedFiltered.value.length
  )

  // =========================
  // FETCH FILTERS
  // =========================
  async function fetchFilters() {
    filtersLoading.value = true
    error.value = null

    const loc = getLocale()
    const cacheKey = `filters_${brandId}_${loc}`

    const cached = get(cacheKey)
    if (cached) {
      filters.value = cached
      filtersLoading.value = false
      return
    }

    try {
      const res = await fetch(buildUrl('filters'), { cache: 'no-store' })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const data = await res.json()

      // ✅ traduci SOLO se serve
      if (data.materials) {
        data.materials = data.materials.map(item => ({
          ...item,
          name: translateValue(MATERIAL_MAP, item.name, loc)
        }))
      }

      if (data.measures) {
        data.measures = data.measures.map(item => ({
          ...item,
          name: translateValue(SIZE_MAP, item.name, loc)
        }))
      }

      filters.value = data
      set(cacheKey, data)

    } catch (e) {
      console.error('[fetchFilters]', e)
      error.value = e.message
    } finally {
      filtersLoading.value = false
    }
  }

  // =========================
  // FETCH PRODUCTS
  // =========================
  async function fetchProducts() {
    loading.value = true
    error.value = null

    const loc = getLocale()
    const cacheKey = buildKey(brandId, { locale: loc })

    const cached = get(cacheKey)
    if (cached) {
      allProducts.value = cached
      loading.value = false
      return
    }

    try {
      const res = await fetch(buildUrl('products'), { cache: 'no-store' })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const data = await res.json()

      const normalized = (Array.isArray(data) ? data : []).map(p => ({
        ...p,
        material: translateValue(MATERIAL_MAP, p.material, loc),
        size: translateValue(SIZE_MAP, p.size, loc),
      }))

      allProducts.value = normalized
      set(cacheKey, normalized)

    } catch (e) {
      console.error('[fetchProducts]', e)
      error.value = e.message
    } finally {
      loading.value = false
    }
  }

  function buildUrl(type) {
    const base = (apiBase || window.location.origin).replace(/\/$/, '')
    const loc = getLocale()

    if (type === 'filters') {
      return `${base}/wp-json/api/v1/products/category/0/brand/${brandId}/filters?locale=${loc}`
    }

    return `${base}/wp-json/api/v1/products/category/0/${brandId}?locale=${loc}&all=true`
  }

  function toggleFilter(group, value) {
    const arr = activeFilters.value[group]
    const idx = arr.indexOf(value)

    if (idx === -1) arr.push(value)
    else arr.splice(idx, 1)

    page.value = 1
  }

  function clearAllFilters() {
    Object.keys(activeFilters.value).forEach(k => {
      activeFilters.value[k] = []
    })
    page.value = 1
  }

  function loadMore() {
    page.value++
  }

  // reload quando cambia lingua
  watch(() => getLocale(), () => {
    clearAllFilters()
    fetchFilters()
    fetchProducts()
  })

  return {
    allProducts,
    filters,
    loading,
    filtersLoading,
    error,
    visibleProducts,
    hasMore,
    totalCount,
    activeFilters,
    sortKey,
    fetchFilters,
    fetchProducts,
    toggleFilter,
    clearAllFilters,
    loadMore,
  }
}