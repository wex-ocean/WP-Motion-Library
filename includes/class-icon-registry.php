<?php
namespace WPML;

defined( 'ABSPATH' ) || exit;

final class Icon_Registry {

    private const TRANSIENT_KEY = 'wpml_icon_registry';

    /** @var array<string, array> */
    private array $registry = [];

    private static ?Icon_Registry $instance = null;

    public static function instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
            self::$instance->load();
        }
        return self::$instance;
    }

    // -------------------------------------------------------------------------
    // Load & Cache
    // -------------------------------------------------------------------------

    private function load(): void {
        $cached = get_transient( self::TRANSIENT_KEY );

        if ( is_array( $cached ) ) {
            $this->registry = $cached;
            return;
        }

        $this->scan_packs();
        set_transient( self::TRANSIENT_KEY, $this->registry, DAY_IN_SECONDS );
    }

    public static function flush_cache(): void {
        delete_transient( self::TRANSIENT_KEY );
    }

    private function scan_packs(): void {
        $icons_dir = WPML_PATH . 'assets/icons/';

        foreach ( glob( $icons_dir . '*/pack.json' ) ?: [] as $json_file ) {
            $raw = file_get_contents( $json_file ); // phpcs:ignore
            if ( ! $raw ) {
                continue;
            }

            $icons = json_decode( $raw, true );
            if ( ! is_array( $icons ) ) {
                continue;
            }

            foreach ( $icons as $icon ) {
                if ( empty( $icon['id'] ) ) {
                    continue;
                }
                $this->registry[ $icon['id'] ] = $this->normalise( $icon );
            }
        }
    }

    private function normalise( array $icon ): array {
        return [
            'id'         => sanitize_key( $icon['id'] ),
            'name'       => sanitize_text_field( $icon['name']     ?? '' ),
            'category'   => sanitize_key( $icon['category']        ?? 'general' ),
            'tags'       => array_map( 'sanitize_key', (array) ( $icon['tags'] ?? [] ) ),
            'type'       => in_array( $icon['type'] ?? '', [ 'svg', 'lottie' ], true ) ? $icon['type'] : 'svg',
            'file'       => sanitize_text_field( $icon['file']     ?? '' ),
            'is_premium' => ! empty( $icon['is_premium'] ),
            'pack'       => sanitize_key( $icon['pack']            ?? '' ),
        ];
    }

    // -------------------------------------------------------------------------
    // Query Methods
    // -------------------------------------------------------------------------

    public function get_all(): array {
        return array_values( $this->registry );
    }

    public function get_by_id( string $id ): ?array {
        return $this->registry[ sanitize_key( $id ) ] ?? null;
    }

    public function get_by_category( string $category ): array {
        $cat = sanitize_key( $category );
        return array_values( array_filter(
            $this->registry,
            fn( $icon ) => $icon['category'] === $cat
        ) );
    }

    public function search( string $term ): array {
        $term = strtolower( sanitize_text_field( $term ) );
        return array_values( array_filter(
            $this->registry,
            function ( $icon ) use ( $term ): bool {
                if ( str_contains( strtolower( $icon['name'] ), $term ) ) {
                    return true;
                }
                foreach ( $icon['tags'] as $tag ) {
                    if ( str_contains( $tag, $term ) ) {
                        return true;
                    }
                }
                return false;
            }
        ) );
    }

    // -------------------------------------------------------------------------
    // Output Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns sanitized SVG markup safe for inline output.
     * Strips <script>, event attributes (on*), and external hrefs.
     */
    public function get_svg_markup( string $id ): string {
        $icon = $this->get_by_id( $id );
        if ( ! $icon || 'svg' !== $icon['type'] ) {
            return '';
        }

        $path = WPML_PATH . 'assets/icons/' . $icon['file'];
        if ( ! file_exists( $path ) ) {
            return '';
        }

        $svg = file_get_contents( $path ); // phpcs:ignore
        return $this->sanitize_svg( $svg );
    }

    /**
     * Returns decoded Lottie JSON array, or null if invalid.
     */
    public function get_lottie_data( string $id ): ?array {
        $icon = $this->get_by_id( $id );
        if ( ! $icon || 'lottie' !== $icon['type'] ) {
            return null;
        }

        $path = WPML_PATH . 'assets/icons/' . $icon['file'];
        if ( ! file_exists( $path ) ) {
            return null;
        }

        $data = json_decode( file_get_contents( $path ), true ); // phpcs:ignore
        // Minimal Lottie structure check: must have 'v' (version) and 'layers' keys.
        if ( ! is_array( $data ) || ! isset( $data['v'], $data['layers'] ) ) {
            return null;
        }

        return $data;
    }

    private function sanitize_svg( string $svg ): string {
        // Remove <script> blocks.
        $svg = preg_replace( '/<script[\s\S]*?<\/script>/i', '', $svg );
        // Remove event attributes (onclick, onload, etc.).
        $svg = preg_replace( '/\s+on\w+="[^"]*"/i', '', $svg );
        $svg = preg_replace( "/\s+on\w+='[^']*'/i", '', $svg );
        // Remove javascript: hrefs.
        $svg = preg_replace( '/href=["\']javascript:[^"\']*["\']/i', '', $svg );

        return trim( $svg );
    }

    private function __construct() {}
    private function __clone() {}
}
