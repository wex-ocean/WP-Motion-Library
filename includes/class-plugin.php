<?php
namespace WPML;

defined( 'ABSPATH' ) || exit;

final class Plugin {

    private static ?Plugin $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        load_plugin_textdomain( 'wp-motion-library', false, dirname( plugin_basename( WPML_FILE ) ) . '/languages' );

        Icon_Registry::instance();
        Rest\Rest_Controller::instance();

        if ( is_admin() ) {
            Admin\Admin::instance();
        }

        add_action( 'after_setup_theme', [ $this, 'load_integrations' ] );
    }

    public function load_integrations(): void {
        if ( did_action( 'elementor/loaded' ) ) {
            Integrations\Elementor::instance();
        }
        if ( class_exists( '\Bricks\Frontend' ) ) {
            Integrations\Bricks::instance();
        }
        if ( class_exists( '\GenerateBlocks' ) ) {
            Integrations\GenerateBlocks::instance();
        }
    }

    public static function activate(): void {
        Icon_Registry::flush_cache();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }

    private function __construct() {}
    private function __clone() {}
}
