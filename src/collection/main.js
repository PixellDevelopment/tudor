import { createApp } from 'vue'
import TudorCollection from './components/TudorCollection.vue'
import './scss/tudor-collection.scss'

// Mount all Tudor collection widgets on the page
document.querySelectorAll('.tudor-collection-app').forEach((el) => {
  const app = createApp(TudorCollection, {
    brandId: el.dataset.brandId || '525',
    locale: el.dataset.locale || document.documentElement.lang || 'it',
    apiBase: el.dataset.apiBase || '',
    ajaxUrl: el.dataset.ajaxUrl || '',
    ajaxNonce: el.dataset.ajaxNonce || '',
  })
  app.mount(el)
})
