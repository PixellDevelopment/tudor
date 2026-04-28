import { computed } from 'vue'
import it from '../locales/it.json'
import en from '../locales/en.json'

const translations = { it, en }

export function useTranslation(locale) {
  const t = computed(() => {
    const lang = locale.value?.substring(0, 2) || 'it'
    return translations[lang] || translations['it']
  })
  return { t }
}
