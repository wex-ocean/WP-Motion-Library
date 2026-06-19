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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <p><?php esc_html_e( 'Welcome to WP Motion Library. More settings coming soon.', 'wp-motion-library' ); ?></p>
        </div>
        <?php
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
    }

    private function __construct() {}
    private function __clone() {}
}
