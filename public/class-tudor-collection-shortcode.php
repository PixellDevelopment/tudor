<?php

/**
 * Tudor Collection Shortcode
 *
 * FIX applicati:
 *  1. type="module" sul tag <script> tramite wp_script_add_data() → Vue ESM monta correttamente
 *  2. Lettura CSS dal manifest: cerca sia entry['css'] (cssCodeSplit:true)
 *     sia la chiave 'style.css' separata (cssCodeSplit:false) → CSS sempre caricato
 *  3. brand_id risolto dinamicamente dalla tassonomia 'product_brand' cercando 'tudor'
 *     → nessun hardcoding
 *  4. enqueue_assets() chiamato tramite hook wp_enqueue_scripts (non dentro render())
 *     così lo script è disponibile anche se il shortcode è in un widget/sidebar
 *
 * Uso nel template:
 *   echo do_shortcode('[tudor_collection]');
 *   echo do_shortcode('[tudor_collection brand_slug="tudor"]');
 *   echo do_shortcode('[tudor_collection brand_id="525"]'); // ancora supportato
 *
 * @package Tudor
 * @since   1.2.0
 */

if (! defined('ABSPATH')) {
    exit;
}

class Tudor_Collection_Shortcode
{

    const HANDLE = 'tudor-collection-vue';

    public function __construct()
    {
        add_shortcode('tudor_collection', [$this, 'render']);

        // Gli asset vengono registrati (non ancora caricati) in wp_enqueue_scripts
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);

        // AJAX: svuota cache (solo utenti admin)
        add_action('wp_ajax_tudor_clear_cache', [$this, 'ajax_clear_cache']);

        // REST proxy con caching server-side (opzionale)
        add_action('rest_api_init', [$this, 'register_rest_routes']);
    }

    // =========================================================================
    // SHORTCODE
    // =========================================================================

    public function render(array $atts = []): string
    {

        $atts = shortcode_atts([
            'brand_slug' => 'tudor',   // cercato nella tassonomia product_brand
            'brand_id'   => '',        // se specificato, bypassa la ricerca per slug
            'locale'     => $this->get_current_locale(),
            'api_base'   => home_url(),
        ], $atts, 'tudor_collection');

        // Risolvi brand_id: usa quello esplicito oppure cerca per slug nella tassonomia
        $brand_id = $this->resolve_brand_id(
            sanitize_text_field($atts['brand_id']),
            sanitize_text_field($atts['brand_slug'])
        );

        if (! $brand_id) {
            // Brand non trovato: mostra un messaggio solo agli admin
            if (current_user_can('manage_options')) {
                return '<p style="color:red;border:1px solid red;padding:8px;">'
                    . '[tudor_collection] — Brand Tudor non trovato nella tassonomia <code>product_brand</code>. '
                    . 'Verifica che WooCommerce e il plugin brands siano attivi e che esista un termine "tudor".'
                    . '</p>';
            }
            return '';
        }

        $locale   = sanitize_text_field($atts['locale']);
        $api_base = esc_url($atts['api_base']);
        $nonce    = wp_create_nonce('tudor_collection');

        // Assicura che gli asset vengano caricati nella pagina corrente
        $this->enqueue_assets();

        return sprintf(
            '<div
                class="tudor-collection-app"
                data-brand-id="%1$s"
                data-locale="%2$s"
                data-api-base="%3$s"
                data-ajax-url="%4$s"
                data-ajax-nonce="%5$s"
            ></div>',
            esc_attr($brand_id),
            esc_attr($locale),
            esc_attr($api_base),
            esc_attr(admin_url('admin-ajax.php')),
            esc_attr($nonce)
        );
    }

    // =========================================================================
    // BRAND ID — risoluzione dinamica dalla tassonomia
    // =========================================================================

    /**
     * Restituisce il term_id del brand Tudor.
     *
     * Ordine di priorità:
     *  1. brand_id esplicito dallo shortcode (se numerico > 0)
     *  2. Cache in wp_options (aggiornata alla prima chiamata o quando svuotata)
     *  3. Ricerca per slug nella tassonomia product_brand
     *  4. Ricerca per name (case-insensitive) in tutte le tassonomie *brand*
     *
     * @param string $explicit_id  brand_id passato direttamente allo shortcode
     * @param string $slug         brand_slug (default 'tudor')
     * @return int|null
     */
    private function resolve_brand_id(string $explicit_id, string $slug): ?int
    {
        // 1. Valore esplicito
        if ($explicit_id !== '' && absint($explicit_id) > 0) {
            return absint($explicit_id);
        }

        // 2. Cache
        $cache_key = 'tudor_brand_id_' . sanitize_key($slug);
        $cached    = get_option($cache_key, null);
        if ($cached !== null && absint($cached) > 0) {
            return absint($cached);
        }

        $candidates = array_unique([$slug, ucfirst($slug), strtoupper($slug)]);

        // 3. Se esiste almeno una tassonomia brand custom → cerca lì
        if (! empty($brand_taxonomies)) {
            foreach ($brand_taxonomies as $taxonomy) {
                foreach ($candidates as $candidate) {
                    $term = get_term_by('slug', $candidate, $taxonomy)
                        ?: get_term_by('name', $candidate, $taxonomy);
                    if ($term && ! is_wp_error($term)) {
                        update_option($cache_key, $term->term_id, false);
                        return $term->term_id;
                    }
                }
            }
            // product_brand esiste ma Tudor non trovato come brand →
            // non scendere su product_cat per evitare di restituire un term_id sbagliato
            return null;  // ← togli questa riga se vuoi il fallback comunque
        }

        // 4. Nessuna tassonomia brand registrata → fallback su product_cat
        if (taxonomy_exists('product_cat')) {
            foreach ($candidates as $candidate) {
                $term = get_term_by('slug', $candidate, 'product_cat')
                    ?: get_term_by('name', $candidate, 'product_cat');
                if ($term && ! is_wp_error($term)) {
                    update_option($cache_key, $term->term_id, false);
                    return $term->term_id;
                }
            }
        }

        return null;
    }

    /**
     * Restituisce tutte le tassonomie di tipo "brand" registrate nel sito.
     * Supporta: product_brand (WooCommerce Brands), pa_brand, pwb-brand, yith_product_brand.
     */
    private function get_brand_taxonomies(): array
    {
        // Tassonomie note
        $known = ['product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand'];

        // Cerca anche qualsiasi tassonomia con 'brand' nel nome registrata dinamicamente
        $registered = get_taxonomies([], 'names');
        $dynamic    = array_filter($registered, fn($t) => str_contains($t, 'brand'));

        $all = array_unique(array_merge($known, array_values($dynamic)));

        // Filtra solo quelle effettivamente registrate
        return array_values(array_filter($all, 'taxonomy_exists'));
    }

    // =========================================================================
    // ASSET MANAGEMENT
    // =========================================================================

    /**
     * Registra JS e CSS nel registry di WordPress.
     * Viene chiamato da wp_enqueue_scripts (hook).
     * L'effettivo enqueue avviene in enqueue_assets() → chiamato da render().
     */
    public function register_assets(): void
    {

        if ($this->is_vite_dev()) {
            return; // In dev mode l'injection avviene in wp_head
        }

        $manifest_path = $this->get_dist_path('.vite/manifest.json');

        if (! file_exists($manifest_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions
                error_log('[Tudor] manifest.json non trovato in: ' . $manifest_path);
            }
            return;
        }

        $manifest  = json_decode(file_get_contents($manifest_path), true);
        $entry_key = 'src/collection/main.js';

        if (empty($manifest[$entry_key])) {
            return;
        }

        $entry    = $manifest[$entry_key];
        $base_url = $this->get_dist_url();

        // ── JavaScript ───────────────────────────────────────────────────────
        // Registriamo lo script normalmente; aggiungeremo type="module" dopo
        wp_register_script(
            self::HANDLE,
            $base_url . $entry['file'],
            [],   // nessuna dipendenza jQuery – Vue ESM è autosufficiente
            null, // versione gestita dall'hash nel filename
            true  // in footer
        );

        // CRITICO: Vue 3 bundle ESM DEVE essere caricato come type="module"
        // senza questo il browser non esegue il codice Vue e il div rimane vuoto.
        wp_script_add_data(self::HANDLE, 'type', 'module');

        // ── CSS ──────────────────────────────────────────────────────────────
        // Con cssCodeSplit:true (default), Vite inserisce il CSS nella chiave 'css'
        // dell'entry JS nel manifest → entries['src/collection/main.js']['css']
        if (! empty($entry['css'])) {
            foreach ($entry['css'] as $i => $css_file) {
                wp_register_style(
                    self::HANDLE . '-style-' . $i,
                    $base_url . $css_file,
                    [],
                    null
                );
            }
        }

        // Fallback: con cssCodeSplit:false Vite emette il CSS come entry separata 'style.css'
        // Gestiamo anche questo caso per retrocompatibilità
        if (empty($entry['css']) && ! empty($manifest['style.css']['file'])) {
            wp_register_style(
                self::HANDLE . '-style-0',
                $base_url . $manifest['style.css']['file'],
                [],
                null
            );
        }
    }

    /**
     * Carica (enqueue) gli asset nella pagina corrente.
     * Chiamato da render() → sicuro anche se il shortcode è in un widget.
     *
     * NOTA: wp_enqueue_script/style chiamati dopo wp_enqueue_scripts funzionano
     * correttamente se siamo ancora nel rendering del template (pre-wp_footer).
     */
    public function enqueue_assets(): void
    {

        if ($this->is_vite_dev()) {
            // Dev mode: inietta il modulo ESM dal Vite dev server
            add_action('wp_head', [$this, 'inject_vite_dev_scripts'], 1);
            return;
        }

        if (wp_script_is(self::HANDLE, 'registered') && ! wp_script_is(self::HANDLE, 'enqueued')) {
            wp_enqueue_script(self::HANDLE);
        }

        // Enqueue tutti gli stili registrati (formato handle-style-0, handle-style-1, …)
        $i = 0;
        while (wp_style_is(self::HANDLE . '-style-' . $i, 'registered')) {
            if (! wp_style_is(self::HANDLE . '-style-' . $i, 'enqueued')) {
                wp_enqueue_style(self::HANDLE . '-style-' . $i);
            }
            $i++;
        }
    }

    /**
     * Inietta i tag <script type="module"> per il Vite dev server.
     */
    public function inject_vite_dev_scripts(): void
    {
        $origin = $this->get_vite_dev_origin();
?>
        <script type="module" src="<?php echo esc_url($origin . '/@vite/client'); ?>"></script>
        <script type="module" src="<?php echo esc_url($origin . '/src/collection/main.js'); ?>"></script>
<?php
    }

    // =========================================================================
    // AJAX — svuota cache
    // =========================================================================

    public function ajax_clear_cache(): void
    {

        check_ajax_referer('tudor_admin_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        // Incrementa versione → invalida tutti i transient con versione precedente
        $version = (int) get_option('tudor_cache_version', 1) + 1;
        update_option('tudor_cache_version', $version, false);

        // Cancella anche la cache del brand_id
        delete_option('tudor_brand_id_tudor');

        // Cancella transient dal DB
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_tudor_%'
                OR option_name LIKE '_transient_timeout_tudor_%'"
        );

        wp_send_json_success([
            'message' => sprintf(__('Cache invalidata. Versione: %d', 'tudor'), $version),
            'version' => $version,
        ]);
    }

    // =========================================================================
    // REST PROXY (opzionale — caching server-side)
    // =========================================================================

    public function register_rest_routes(): void
    {

        $args = [
            'brand_id' => ['default' => 0,    'sanitize_callback' => 'absint'],
            'locale'   => ['default' => 'it', 'sanitize_callback' => 'sanitize_text_field'],
        ];

        register_rest_route('tudor/v1', '/filters', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'rest_get_filters'],
            'permission_callback' => '__return_true',
            'args'                => $args,
        ]);

        register_rest_route('tudor/v1', '/products', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'rest_get_products'],
            'permission_callback' => '__return_true',
            'args'                => $args,
        ]);
    }

    public function rest_get_filters(WP_REST_Request $request): WP_REST_Response
    {
        return $this->proxy_request(
            "tudor_filters_{$request->get_param('brand_id')}_{$request->get_param('locale')}",
            home_url("/wp-json/api/v1/products/category/0/brand/{$request->get_param('brand_id')}/filters?promo=&locale={$request->get_param('locale')}"),
            10
        );
    }

    public function rest_get_products(WP_REST_Request $request): WP_REST_Response
    {
        return $this->proxy_request(
            "tudor_products_{$request->get_param('brand_id')}_{$request->get_param('locale')}",
            home_url("/wp-json/api/v1/products/category/0/{$request->get_param('brand_id')}?locale={$request->get_param('locale')}&promo=&"),
            15
        );
    }

    private function proxy_request(string $base_key, string $url, int $timeout): WP_REST_Response
    {
        $cache_v   = (int) get_option('tudor_cache_version', 1);
        $trans_key = $base_key . '_v' . $cache_v;

        $cached = get_transient($trans_key);
        if (false !== $cached) {
            return new WP_REST_Response($cached, 200);
        }

        $response = wp_remote_get($url, ['timeout' => $timeout]);

        if (is_wp_error($response)) {
            return new WP_REST_Response(['error' => $response->get_error_message()], 500);
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($trans_key, $data, 30 * MINUTE_IN_SECONDS);

        return new WP_REST_Response($data, 200);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function get_current_locale(): string
    {
        if (function_exists('icl_get_current_language')) {
            return (string) icl_get_current_language();
        }
        if (function_exists('pll_current_language')) {
            return (string) pll_current_language('slug');
        }
        return substr(get_locale(), 0, 2);
    }

    private function get_dist_path(string $file = ''): string
    {
        return TUDOR_ROOT . '/dist/collection/' . ltrim($file, '/');
    }

    private function get_dist_url(string $file = ''): string
    {
        // plugin_dir_url(__FILE__) = .../tudor/public/
        // dirname() sale a .../tudor/
        return plugin_dir_url(dirname(__FILE__)) . 'dist/collection/' . ltrim($file, '/');
    }

    private function is_vite_dev(): bool
    {
        if (! defined('VITE_DEVELOPMENT') || true !== VITE_DEVELOPMENT) {
            return false;
        }
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return str_contains($host, '.pixelldemo')
            || str_contains($host, '.test')
            || str_contains($host, 'localhost');
    }

    private function get_vite_dev_origin(): string
    {
        // Stesso dominio HMR del Vite principale ma porta 5174
        // Oppure usa wss:// se il proxy SSL è attivo (come nel tuo setup Docker/nginx)
        if (defined('VITE_COLLECTION_ORIGIN')) {
            return VITE_COLLECTION_ORIGIN;
        }
        // Default: stesso host del Vite principale ma su wss/443 (setup nginx proxy)
        return 'https://vite.menichelli.pixelldemo.com';
    }
}

new Tudor_Collection_Shortcode();
