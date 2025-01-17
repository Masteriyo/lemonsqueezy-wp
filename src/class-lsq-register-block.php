<?php

namespace lemonsqueezy;

/**
 * Class to handle rest api connections of the lemon squeezy plugin.
 */
class LSQ_Register_Block {
	/**
	 * Contains instance or null
	 *
	 * @var object|null
	 */
	private static $instance = null;

	/**
	 * Returns instance of LSQ_Rest.
	 *
	 * @return object
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor for LSQ_Register_Block.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'render_block', array( $this, 'filter_button_block_markup' ), 10, 2 );
		add_filter( 'block_categories_all', array( $this, 'add_block_categories' ), 10, 2 );
	}

	/**
	 * Register block category.
	 *
	 * @param array  $categories given categories.
	 * @param object $post current post.
	 * @return array
	 */
	public function add_block_categories( $categories, $post ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'lemonsqueezy',
					'title' => __( 'Lemon Squeezy', 'lemonsqueezy' ),
					'icon'  => 'wordpress',
				),
			)
		);
	}

	/**
	 * Register block types.
	 *
	 * @param string $block current block.
	 * @param array  $options list of options.
	 * @return void
	 */
	public function register_block_type( $block, $options = array() ) {
		register_block_type(
			'lemonsqueezy/' . $block,
			array_merge(
				array(
					'editor_script' => 'lemonsqueezy-editor-script',
					'editor_style'  => 'lemonsqueezy-editor-style',
					'script'        => 'lemonsqueezy-script',
					'style'         => 'lemonsqueezy-style',
				),
				$options
			)
		);
	}

	/**
	 * Register lemon squeezy blocks and enqueue assets.
	 *
	 * @return void
	 */
	public function register_blocks() {
		wp_register_script( 'lemonsqueezy-editor-script', LSQ_URL . '/dist/editor.js', array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'lodash', 'wp-blob', 'wp-data', 'wp-html-entities', 'wp-compose', 'wp-block-editor' ), '1.0.0', true );
		wp_add_inline_script( 'lemonsqueezy-editor-script', 'var lsData = ' . wp_json_encode( array( 'settings_url' => admin_url( 'admin.php?page=lemonsqueezy' ) ) ), 'before' );
		wp_register_script( 'lemonsqueezy-script', LSQ_URL . '/dist/script.js', array(), '1.0.0', true );
		wp_register_style( 'lemonsqueezy-style', LSQ_URL . '/dist/style.css', array(), '1.0.0' );
		wp_register_style( 'lemonsqueezy-editor-style', LSQ_URL . '/dist/editor.css', array( 'wp-edit-blocks' ), '1.0.0' );

		$this->register_block_type( 'button' );
		$this->register_block_type( 'ls-button' );

	}

	/**
	 * Add button size class.
	 *
	 * @param  string $block_content Block content to be rendered.
	 * @param  array  $block         Block attributes.
	 * @return string
	 */
	public function filter_button_block_markup( $block_content = '', $block = array() ) {
		if ( isset( $block['blockName'] ) && 'core/button' === $block['blockName'] ) {
			$args = wp_parse_args( $block['attrs'] );

			if ( empty( $args['product'] ) ) {
				return $block_content;
			}

			$purchase_link = $args['product'];
			$classes = 'wp-block-button__link';

			// If overlay is activated we have to include the script and add parameter to URL.
			if ( ! empty( $args['overlay'] ) ) {
				wp_enqueue_script( 'lemonsqueezy-checkout', 'https://app.lemonsqueezy.com/js/checkout.js', array(), null, true );

				$purchase_link = $purchase_link . '?embed=1';
				$classes .= ' lemonsqueezy-button';
			}

			$block_content = str_replace( '<a class="wp-block-button__link">', '<a class="' . $classes . '" href="' . $purchase_link . '">', $block_content );
		}
		if ( isset( $block['blockName'] ) && 'lemonsqueezy/ls-button' === $block['blockName'] ) {
			$args = wp_parse_args( $block['attrs'] );

			if ( ! empty( $args['overlay'] ) ) {
				wp_enqueue_script( 'lemonsqueezy-checkout', 'https://app.lemonsqueezy.com/js/checkout.js', array(), null, true );
			}
		}

		return $block_content;
	}
}
