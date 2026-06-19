<?php
namespace WPML\Integrations;

defined( 'ABSPATH' ) || exit;

final class Elementor {

    private static ?Elementor $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        // TODO: register Elementor widgets / extensions.
    }

    private function __construct() {}
    private function __clone() {}
}
