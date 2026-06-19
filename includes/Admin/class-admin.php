<?php
namespace WPML\Admin;

defined( 'ABSPATH' ) || exit;

final class Admin {

    private static ?Admin $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'WP Motion Library', 'wp-motion-library' ),
            __( 'Motion Library', 'wp-motion-library' ),
            'manage_options',
            'wp-motion-library',
            [ $this, 'render_page' ],
            'dashicons-playlist-video',
            80
        );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="wrap"><h1>' . esc_html( get_admin_page_title() ) . '</h1>'
           . '<div id="wpml-admin-root"></div></div>';
    }

    public function enqueue_assets( string $hook ): void {
        if ( 'toplevel_page_wp-motion-library' !== $hook ) {
            return;
        }

        wp_enqueue_style(
            'wpml-admin',
            WPML_URL . 'assets/css/admin.css',
            [],
            WPML_VERSION
        );

        wp_enqueue_script(
            'wpml-admin-app',
            WPML_URL . 'assets/js/admin-app.js',
            [ 'wp-element' ],
            WPML_VERSION,
            true
        );

        wp_localize_script( 'wpml-admin-app', 'wpmlAdmin', [
            'restUrl'  => esc_url_raw( rest_url( 'wpml/v1/icons' ) ),
            'nonce'    => wp_create_nonce( 'wp_rest' ),
            'iconsUrl' => esc_url_raw( WPML_URL . 'assets/icons/' ),
            'isPremium' => false, // flip to true when license is active.
        ] );
    }

    private function __construct() {}
    private function __clone() {}
}
