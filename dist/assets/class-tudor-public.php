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
				return pxl_translate('Scopri l’orologio TUDOR', 'Discover the TUDOR watch') . ' ' . get_the_title() .  ' ' . $tudor_attributes['Reference'] . pxl_translate('presentato presso', 'presented at') . get_bloginfo('name');
			default:
				return '';
		}
	}
}
