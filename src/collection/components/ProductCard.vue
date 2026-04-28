<template>
  <a :href="product.url" class="tudor-card" :class="{ 'tudor-card--loading': !imageLoaded }">
    <div class="tudor-card__image-wrap">
      <div class="tudor-card__skeleton" v-if="!imageLoaded" />
      <img
        :src="imageSrc"
        :alt="product.model_name"
        class="tudor-card__image"
        :class="{ 'tudor-card__image--visible': imageLoaded }"
        loading="lazy"
        @load="imageLoaded = true"
        @error="imageLoaded = true"
      />
    </div>
    <div class="tudor-card__body">
      <span class="tudor-card__brand">TUDOR</span>
      <h3 class="tudor-card__name">{{ product.model_name }}</h3>
      <p class="tudor-card__price">{{ formattedPrice }}</p>
    </div>
  </a>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  product: { type: Object, required: true },
})

const imageLoaded = ref(false)

const imageSrc = computed(() => {
  const img = props.product.main_image || ''
  const match = img.match(/src='([^']+)'/)
  return match ? match[1] : ''
})

const formattedPrice = computed(() => {
  const p = parseFloat(props.product.price)
  if (isNaN(p)) return ''
  const formatted = Math.round(p)
    .toString()
    .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
  return `${formatted} €`
})
</script>
