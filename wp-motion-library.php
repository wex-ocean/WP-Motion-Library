<?php
/**
 * Plugin Name:       WP Motion Library
 * Plugin URI:        https://example.com/wp-motion-library
 * Description:       A lightweight motion/animation library for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-motion-library
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'WPML_VERSION', '1.0.0' );
define( 'WPML_FILE',    __FILE__ );
define( 'WPML_PATH',    plugin_dir_path( __FILE__ ) );
define( 'WPML_URL',     plugin_dir_url( __FILE__ ) );

/**
 * PSR-4 autoloader: WPML\ → includes/
 * e.g. WPML\Admin\Admin    → includes/Admin/class-admin.php
 *      WPML\Plugin         → includes/class-plugin.php
 */
spl_autoload_register( function ( string $class ): void {
    $prefix = 'WPML\\';
    if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
        return;
    }

    $relative = substr( $class, strlen( $prefix ) ); // e.g. "Admin\Admin"
    $parts    = explode( '\\', $relative );
    $name     = array_pop( $parts );                 // e.g. "Admin"
    $sub_dir  = $parts ? implode( '/', $parts ) . '/' : ''; // e.g. "Admin/"

    $file = WPML_PATH . 'includes/' . $sub_dir . 'class-' . strtolower( $name ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

register_activation_hook(   __FILE__, [ 'WPML\\Plugin', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'WPML\\Plugin', 'deactivate' ] );

add_action( 'plugins_loaded', function (): void {
    WPML\Plugin::instance();
} );
