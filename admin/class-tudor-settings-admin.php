<?php
/**
 * Tudor Settings – Pagina admin unificata (Cache + Impostazioni).
 *
 * @package Tudor
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tudor_Settings_Admin {

    const OPTION_CF7_SHORTCODE    = 'tudor_cf7_shortcode';
    const OPTION_CF7_SHORTCODE_EN = 'tudor_cf7_shortcode_en';

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_post_tudor_save_settings', [ $this, 'save_settings' ] );
    }

    public function register_menu(): void {
        add_submenu_page(
            'options-general.php',
            __( 'Tudor Settings', 'tudor' ),
            __( 'Tudor Settings', 'tudor' ),
            'manage_options',
            'tudor-cache',
            [ $this, 'render_page' ]
        );
    }

    public function enqueue_scripts( string $hook ): void {
        if ( 'settings_page_tudor-cache' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'tudor-cache-admin',
            plugin_dir_url( dirname( __FILE__ ) ) . 'admin/js/tudor-cache-admin.js',
            [ 'jquery' ],
            TUDOR_VERSION,
            true
        );

        wp_localize_script( 'tudor-cache-admin', 'tudorCacheAdmin', [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'tudor_admin_nonce' ),
            'i18n'    => [
                'clearing' => __( 'Svuotamento in corso…', 'tudor' ),
                'cleared'  => __( 'Cache svuotata con successo!', 'tudor' ),
                'error'    => __( 'Errore durante lo svuotamento della cache.', 'tudor' ),
            ],
        ] );
    }

    public function save_settings(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Permessi insufficienti.', 'tudor' ) );
        }

        check_admin_referer( 'tudor_save_settings' );

        $shortcode_it = isset( $_POST[ self::OPTION_CF7_SHORTCODE ] )
            ? sanitize_text_field( wp_unslash( $_POST[ self::OPTION_CF7_SHORTCODE ] ) )
            : '';

        $shortcode_en = isset( $_POST[ self::OPTION_CF7_SHORTCODE_EN ] )
            ? sanitize_text_field( wp_unslash( $_POST[ self::OPTION_CF7_SHORTCODE_EN ] ) )
            : '';

        update_option( self::OPTION_CF7_SHORTCODE,    $shortcode_it );
        update_option( self::OPTION_CF7_SHORTCODE_EN, $shortcode_en );

        wp_redirect( add_query_arg( [
            'page'    => 'tudor-cache',
            'tab'     => 'settings',
            'updated' => '1',
        ], admin_url( 'options-general.php' ) ) );
        exit;
    }

    public function render_page(): void {

        $cache_version    = (int) get_option( 'tudor_cache_version', 1 );
        $cf7_shortcode    = get_option( self::OPTION_CF7_SHORTCODE, '' );
        $cf7_shortcode_en = get_option( self::OPTION_CF7_SHORTCODE_EN, '' );
        $active_tab       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'cache';
        $updated          = isset( $_GET['updated'] ) && '1' === $_GET['updated'];

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Tudor — Impostazioni', 'tudor' ); ?></h1>
            <hr class="wp-header-end">

            <!-- Tab navigation -->
            <nav class="nav-tab-wrapper" style="margin-top:16px">
                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'tudor-cache', 'tab' => 'cache' ], admin_url( 'options-general.php' ) ) ); ?>"
                   class="nav-tab <?php echo $active_tab === 'cache' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '🗄 Cache', 'tudor' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'tudor-cache', 'tab' => 'settings' ], admin_url( 'options-general.php' ) ) ); ?>"
                   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '⚙️ Impostazioni', 'tudor' ); ?>
                </a>
                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'tudor-cache', 'tab' => 'docs' ], admin_url( 'options-general.php' ) ) ); ?>"
                   class="nav-tab <?php echo $active_tab === 'docs' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( '📖 Documentazione', 'tudor' ); ?>
                </a>
            </nav>

            <div style="margin-top:24px">

                <?php if ( $active_tab === 'cache' ) : ?>
                <!-- ===== TAB: CACHE ===== -->
                <div class="card" style="max-width:640px;padding:24px 28px;">
                    <h2 style="margin-top:0"><?php esc_html_e( 'Cache prodotti Tudor', 'tudor' ); ?></h2>

                    <p><?php esc_html_e( 'I dati (prodotti e filtri) vengono memorizzati in cache a due livelli:', 'tudor' ); ?></p>
                    <ul style="list-style:disc;padding-left:20px;color:#3c3c3c">
                        <li><strong><?php esc_html_e( 'Server', 'tudor' ); ?></strong> — WordPress transient, durata 30 minuti.</li>
                        <li><strong><?php esc_html_e( 'Browser', 'tudor' ); ?></strong> — localStorage, durata 30 minuti, invalidato automaticamente al cambio versione.</li>
                    </ul>

                    <table class="form-table" style="margin-top:16px">
                        <tr>
                            <th><?php esc_html_e( 'Versione cache corrente', 'tudor' ); ?></th>
                            <td><code id="tudor-cache-version" style="font-size:16px;font-weight:bold"><?php echo esc_html( $cache_version ); ?></code></td>
                        </tr>
                    </table>

                    <p style="margin-top:20px">
                        <button id="tudor-clear-cache-btn" class="button button-primary button-large">
                            <?php esc_html_e( '🗑 Svuota cache Tudor', 'tudor' ); ?>
                        </button>
                    </p>

                    <div id="tudor-cache-message" style="display:none;margin-top:12px"></div>
                </div>

                <?php elseif ( $active_tab === 'settings' ) : ?>
                <!-- ===== TAB: IMPOSTAZIONI ===== -->

                <?php if ( $updated ) : ?>
                    <div class="notice notice-success is-dismissible" style="max-width:640px">
                        <p><?php esc_html_e( 'Impostazioni salvate con successo.', 'tudor' ); ?></p>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( 'tudor_save_settings' ); ?>
                    <input type="hidden" name="action" value="tudor_save_settings">

                    <div class="card" style="max-width:640px;padding:24px 28px;">
                        <h2 style="margin-top:0"><?php esc_html_e( 'Pagina Contatti', 'tudor' ); ?></h2>
                        <p class="description" style="margin-bottom:16px">
                            <?php esc_html_e( 'Inserisci gli shortcode di Contact Form 7 per ciascuna lingua. Verranno richiamati automaticamente nel template in base a ICL_LANGUAGE_CODE.', 'tudor' ); ?>
                        </p>

                        <table class="form-table">

                            <!-- IT -->
                            <tr>
                                <th scope="row">
                                    <label for="tudor_cf7_shortcode">
                                        <?php esc_html_e( 'Shortcode CF7 — Italiano 🇮🇹', 'tudor' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        type="text"
                                        id="tudor_cf7_shortcode"
                                        name="<?php echo esc_attr( self::OPTION_CF7_SHORTCODE ); ?>"
                                        value="<?php echo esc_attr( $cf7_shortcode ); ?>"
                                        class="regular-text"
                                        placeholder='[contact-form-7 id="123" title="Contatti Tudor IT"]'
                                    >
                                    <p class="description">
                                        <?php esc_html_e( 'Mostrato quando ICL_LANGUAGE_CODE = it (fallback predefinito).', 'tudor' ); ?>
                                    </p>
                                    <?php if ( $cf7_shortcode ) : ?>
                                        <div style="margin-top:10px;padding:10px 14px;background:#f0f0f1;border-radius:4px">
                                            <strong><?php esc_html_e( 'Anteprima:', 'tudor' ); ?></strong><br>
                                            <code style="font-size:13px"><?php echo esc_html( $cf7_shortcode ); ?></code>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- EN -->
                            <tr>
                                <th scope="row">
                                    <label for="tudor_cf7_shortcode_en">
                                        <?php esc_html_e( 'Shortcode CF7 — English 🇬🇧', 'tudor' ); ?>
                                    </label>
                                </th>
                                <td>
                                    <input
                                        type="text"
                                        id="tudor_cf7_shortcode_en"
                                        name="<?php echo esc_attr( self::OPTION_CF7_SHORTCODE_EN ); ?>"
                                        value="<?php echo esc_attr( $cf7_shortcode_en ); ?>"
                                        class="regular-text"
                                        placeholder='[contact-form-7 id="456" title="Contatti Tudor EN"]'
                                    >
                                    <p class="description">
                                        <?php esc_html_e( 'Mostrato quando ICL_LANGUAGE_CODE = en.', 'tudor' ); ?>
                                    </p>
                                    <?php if ( $cf7_shortcode_en ) : ?>
                                        <div style="margin-top:10px;padding:10px 14px;background:#f0f0f1;border-radius:4px">
                                            <strong><?php esc_html_e( 'Anteprima:', 'tudor' ); ?></strong><br>
                                            <code style="font-size:13px"><?php echo esc_html( $cf7_shortcode_en ); ?></code>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        </table>

                        <p style="margin-top:20px">
                            <?php submit_button( __( 'Salva impostazioni', 'tudor' ), 'primary large', 'submit', false ); ?>
                        </p>
                    </div>
                </form>

                <?php elseif ( $active_tab === 'docs' ) : ?>
                <!-- ===== TAB: DOCUMENTAZIONE ===== -->
                <div class="card" style="max-width:640px;padding:24px 28px;">
                    <h2 style="margin-top:0"><?php esc_html_e( 'Come usare il componente', 'tudor' ); ?></h2>

                    <h3><?php esc_html_e( 'Shortcode (editor WordPress)', 'tudor' ); ?></h3>
                    <pre style="background:#f0f0f1;padding:12px 16px;border-radius:4px;overflow-x:auto">[tudor_collection brand_id="525"]</pre>

                    <h3><?php esc_html_e( 'Template PHP', 'tudor' ); ?></h3>
                    <pre style="background:#f0f0f1;padding:12px 16px;border-radius:4px;overflow-x:auto">&lt;?php echo do_shortcode('[tudor_collection brand_id="525"]'); ?&gt;</pre>

                    <h3><?php esc_html_e( 'Parametri disponibili', 'tudor' ); ?></h3>
                    <table class="widefat striped fixed" style="margin-top:8px">
                        <thead>
                            <tr>
                                <th style="width:140px"><?php esc_html_e( 'Parametro', 'tudor' ); ?></th>
                                <th style="width:100px"><?php esc_html_e( 'Default', 'tudor' ); ?></th>
                                <th><?php esc_html_e( 'Descrizione', 'tudor' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>brand_id</code></td>
                                <td><code>525</code></td>
                                <td><?php esc_html_e( 'ID brand Tudor nella tassonomia WooCommerce', 'tudor' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>locale</code></td>
                                <td><?php esc_html_e( 'auto', 'tudor' ); ?></td>
                                <td><?php esc_html_e( 'Lingua (it/en). Rilevata da WPML/Polylang se attivi.', 'tudor' ); ?></td>
                            </tr>
                            <tr>
                                <td><code>api_base</code></td>
                                <td><?php esc_html_e( 'URL sito', 'tudor' ); ?></td>
                                <td><?php esc_html_e( 'Base URL API REST. Lascia vuoto per usare il sito corrente.', 'tudor' ); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card" style="max-width:640px;margin-top:24px;padding:24px 28px;">
                    <h2 style="margin-top:0"><?php esc_html_e( 'REST Proxy (opzionale)', 'tudor' ); ?></h2>
                    <p><?php esc_html_e( 'Il plugin espone due endpoint proxy con caching server-side:', 'tudor' ); ?></p>
                    <pre style="background:#f0f0f1;padding:12px 16px;border-radius:4px;overflow-x:auto;font-size:12px"><?php echo esc_html( home_url( '/wp-json/tudor/v1/filters?brand_id=525&locale=it' ) ); ?>
<?php echo esc_html( home_url( '/wp-json/tudor/v1/products?brand_id=525&locale=it' ) ); ?></pre>
                    <p class="description"><?php esc_html_e( 'Questi endpoint includono il caching server-side (transient WP 30 min) e vengono invalidati dal pulsante "Svuota cache" qui sopra.', 'tudor' ); ?></p>
                </div>

                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

new Tudor_Settings_Admin();