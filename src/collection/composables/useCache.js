const CACHE_PREFIX = 'tudor_cache_'
const CACHE_TTL = 1000 * 60 * 30 // 30 minutes default

export function useCache() {
  function getCacheKey(key) {
    return CACHE_PREFIX + key
  }

  function get(key) {
    try {
      const raw = localStorage.getItem(getCacheKey(key))
      if (!raw) return null
      const { data, expires } = JSON.parse(raw)
      if (Date.now() > expires) {
        localStorage.removeItem(getCacheKey(key))
        return null
      }
      return data
    } catch {
      return null
    }
  }

  function set(key, data, ttl = CACHE_TTL) {
    try {
      localStorage.setItem(getCacheKey(key), JSON.stringify({
        data,
        expires: Date.now() + ttl,
      }))
    } catch {
      // Storage full or unavailable – silently skip
    }
  }

  function clear(key) {
    if (key) {
      localStorage.removeItem(getCacheKey(key))
    } else {
      // Clear all tudor cache entries
      Object.keys(localStorage)
        .filter(k => k.startsWith(CACHE_PREFIX))
        .forEach(k => localStorage.removeItem(k))
    }
  }

  // Build a deterministic cache key from filter params
  function buildKey(brandId, params) {
    const sorted = Object.entries(params)
      .filter(([, v]) => v !== '' && v !== null && v !== undefined)
      .sort(([a], [b]) => a.localeCompare(b))
      .map(([k, v]) => `${k}=${v}`)
      .join('&')
    return `products_${brandId}_${sorted}`
  }

  return { get, set, clear, buildKey }
}
