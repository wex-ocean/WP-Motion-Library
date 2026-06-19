<?php
namespace WPML\Integrations;

defined( 'ABSPATH' ) || exit;

final class GenerateBlocks {

    private static ?GenerateBlocks $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        // TODO: register GenerateBlocks extensions.
    }

    private function __construct() {}
    private function __clone() {}
}
