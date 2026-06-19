<?php
namespace WPML\Rest;

defined( 'ABSPATH' ) || exit;

final class Rest_Controller {

    private static ?Rest_Controller $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    private function init(): void {
        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    public function register_routes(): void {
        register_rest_route( 'wpml/v1', '/icons', [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_icons' ],
            'permission_callback' => [ $this, 'permissions' ],
            'args'                => [
                'search'   => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default'           => '',
                ],
                'category' => [
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_key',
                    'default'           => '',
                ],
            ],
        ] );
    }

    public function permissions(): bool {
        return current_user_can( 'manage_options' );
    }

    public function get_icons( \WP_REST_Request $request ): \WP_REST_Response {
        $registry = \WPML\Icon_Registry::instance();
        $search   = $request->get_param( 'search' );
        $category = $request->get_param( 'category' );

        if ( '' !== $search ) {
            $icons = $registry->search( $search );
        } elseif ( '' !== $category ) {
            $icons = $registry->get_by_category( $category );
        } else {
            $icons = $registry->get_all();
        }

        return new \WP_REST_Response( $icons, 200 );
    }

    private function __construct() {}
    private function __clone() {}
}
