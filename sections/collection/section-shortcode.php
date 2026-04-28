<main class="tudor-page tudor-page--collection" role="main">
  <?php
  /**
   * Shortcode [tudor_collection]
   * Parametri:
   *   brand_id  = ID brand Tudor (default 525)
   *   locale    = lingua (rilevata automaticamente da WPML/Polylang)
   *   api_base  = URL base API (default: URL del sito corrente)
   *
   * Esempio con api esterna:
   * echo do_shortcode('[tudor_collection brand_id="525" api_base="https://menichelli.pixelldemo.com"]');
   */
  echo do_shortcode('[tudor_collection brand_slug="tudor"]');
  ?>
</main>