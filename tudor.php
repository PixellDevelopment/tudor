<?php
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://pixell.it
 * @since             1.0.0
 * @package           Tudor
 *
 * @wordpress-plugin
 * Plugin Name:       Tudor
 * Plugin URI:        https://pixell.it
 * Description:       Nuovo plugin per il Tudor Bespoke
 * Version:           1.0.0
 * Author:            Pixell
 * Author URI:        https://pixell.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tudor
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TUDOR_VERSION', '1.0.0');
define('TUDOR_ROOT', __DIR__);
define('TUDOR_FILE', plugins_url('', __FILE__));

define('VITE_DEVELOPMENT', false);
define('VITE_HOST', 'vite.menichelli.pixelldemo.com');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tudor-activator.php
 */
function activate_tudor()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-tudor-activator.php';
	Tudor_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tudor-deactivator.php
 */
function deactivate_tudor()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-tudor-deactivator.php';
	Tudor_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_tudor');
register_deactivation_hook(__FILE__, 'deactivate_tudor');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-tudor.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tudor()
{

	$plugin = new Tudor();
	$plugin->run();
}
run_tudor();
