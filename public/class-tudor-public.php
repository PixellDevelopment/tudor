<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://pixell.it
 * @since      1.0.0
 *
 * @package    Tudor
 * @subpackage Tudor/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tudor
 * @subpackage Tudor/public
 * @author     Pixell <dev@pixell.it>
 */
class Tudor_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Tudor_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Tudor_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tudor-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/tudor-contact.js',
			array('jquery'),
			$this->version,
			false
		);

		wp_localize_script($this->plugin_name, 'tudorConfig', [
			'contactUrl' => get_permalink(get_page_by_path('contatti')),
		]);
	}


	/**
	 * Genera un meta tag personalizzato in base al tipo.
	 *
	 * @param string $value Contenuto.
	 *
	 * @return string Ritorna un valore personalizzato.
	 */
	function dynamic_seo(string $value)
	{
		$hook = current_filter();
		$type = match ($hook) {
			'wpseo_title' => 'title',
			'wpseo_metadesc' => 'description',
			default => 'title'
		};
		$prod_id = get_the_ID();
		$locale = ICL_LANGUAGE_CODE == 'it' ? 'it' : 'en';
		$prod_obj = wc_get_product($prod_id);
		$is_tudor = check_is_tudor();

		// la seo personalizzata viene caricata solo se si tratta di un prodotto
		if ($prod_obj && $is_tudor) {
			return $this->get_seo_tudor($type, $locale, $prod_id);
		}

		return $value;
	}


	function get_seo_tudor($type, $locale, $prod_id)
	{
		global $post;

		$tudor_attributes = get_tudor_attributes($post->ID);

		switch ($type) {
			// sui tudor la referenza è il post_title
			case 'title':
				return pxl_translate('Orologio', 'Watches') . ' ' . get_the_title() .  ' | ' . get_bloginfo('name');
			case 'description':
				//cassa in acciaio, 36 mm, lunetta con diamanti
				return pxl_translate("Scopri l'orologio TUDOR", 'Discover the TUDOR watch') . ' ' . get_the_title() .  ' ' . $tudor_attributes['Reference'] . pxl_translate('presentato presso', 'presented at') . get_bloginfo('name');
			default:
				return '';
		}
	}

	/**
	 * Fix WPML language switcher URLs on Tudor product pages.
	 *
	 * Elementor Pro theme builder changes the global $post during header rendering,
	 * so the WPML language switcher widget reads the Elementor template post instead
	 * of the actual product post, generating the same URL for all languages.
	 * We use $wp_the_query (never overridden by Elementor) to find the real product
	 * and rebuild the correct per-language URLs.
	 */
	public function fix_wpml_language_switcher_urls(string $url, array $data): string
	{
		static $running = false;
		if ($running) {
			return $url;
		}

		global $wp_the_query;

		if (
			! $wp_the_query ||
			! $wp_the_query->is_singular('product') ||
			! function_exists('icl_object_id')
		) {
			return $url;
		}

		$product_id = (int) $wp_the_query->queried_object_id;
		if (! $product_id) {
			return $url;
		}

		$lang_code = $data['language_code'] ?? ($data['code'] ?? '');
		if (! $lang_code) {
			return $url;
		}

		$translated_id = (int) icl_object_id($product_id, 'product', false, $lang_code);
		if (! $translated_id) {
			return $url;
		}

		$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'it';

		$running = true;
		do_action('wpml_switch_language', $lang_code);
		$new_url = get_permalink($translated_id);
		do_action('wpml_switch_language', $current_lang);
		$running = false;

		return $new_url ?: $url;
	}

	/**
	 * JS fallback: fix WPML language switcher links in footer.
	 * Runs client-side after HTML is fully rendered, bypassing any
	 * PHP-level cache (WPML LS model cache, Elementor, page cache).
	 */
	public function fix_wpml_switcher_via_js(): void
	{
		global $wp_the_query;

		if (
			! $wp_the_query ||
			! $wp_the_query->is_singular('product') ||
			! function_exists('icl_object_id')
		) {
			return;
		}

		$product_id   = (int) $wp_the_query->queried_object_id;
		$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'it';

		if (! $product_id) {
			return;
		}

		$fixes = [];
		$active_langs = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);

		if (! is_array($active_langs)) {
			return;
		}

		foreach ($active_langs as $lang_code => $lang_data) {
			if ($lang_code === $current_lang) {
				continue;
			}
			$translated_id = (int) icl_object_id($product_id, 'product', false, $lang_code);
			if (! $translated_id) {
				continue;
			}
			do_action('wpml_switch_language', $lang_code);
			$url = get_permalink($translated_id);
			do_action('wpml_switch_language', $current_lang);
			if ($url) {
				$fixes[$lang_code] = $url;
			}
		}

		if (empty($fixes)) {
			return;
		}

		echo '<script>';
		foreach ($fixes as $lang_code => $url) {
			printf(
				'document.querySelectorAll(".wpml-ls-item-%s a.wpml-ls-link").forEach(function(a){a.href="%s";});',
				esc_js($lang_code),
				esc_url($url)
			);
		}
		echo '</script>';
	}
}
