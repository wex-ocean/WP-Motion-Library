<?php
namespace WPML\Integrations;

defined( 'ABSPATH' ) || exit;

final class Bricks {

    private static ?Bricks $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        // TODO: register Bricks elements / extensions.
    }

    private function __construct() {}
    private function __clone() {}
}
