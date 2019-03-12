<?php
/**
 * child theme
 *
 * @package theme
 * @subpackage codename
 * @version 1.0
 */

namespace theme;

use \theme\Theme;
use \theme\Options;


/**
 * Theme child main class
 */
class Child {

	// @type object $theme - \theme\Theme
	private $theme;

	// @type string $prefix
	public $asset_prefix;

	// @type string $suffix
	public $asset_suffix;

	// @type string $bs_ver
	public $bs_ver;

	// @type string $bst
	public $bst = null;

	// Sets Bootstrap version
	// @type string BS_VERSION
	public const BS_VERSION = '4.2.1';

	// Sets Bootswatch data transient time expiration
	// @type string BTS_CACHE_TIMEOUT
	public const BTS_CACHE_TIMEOUT = 604800;


	/**
	 * Function __construct
	 */
	function __construct() {

		add_action( 'init', array($this, 'initialize'), 0 );
		add_action( 'after_setup_theme', array($this, 'setup') );

		add_filter( 'get_custom_logo', array($this, 'site_logo') );

	}

	/**
	 * Initialize
	 */
	public function initialize() {

		$this->theme = Theme::instance();

		$this->asset_prefix = $this->theme->Setup->asset_prefix;
		$this->asset_suffix = $this->theme->Setup->asset_suffix;

		if ( $this->theme->Functions->has_shop( 'WooCommerce' ) )
			$this->shop_wc_mods();

		// Removes custom logo support
		remove_theme_support( 'custom-logo' );

		// Conditional loads Boostrap themes theme support
		if ( current_theme_supports( 'bootstrap-themes' ) ) {
			add_action( 'wp_enqueue_scripts', array($this, 'bst_queue'), 20 );
			add_action( 'theme_settings_frontend_section', array($this, 'bst_options') );
			add_action( 'customize_register', array($this, 'bst_customizer') );
		}

		add_action( 'wp_enqueue_scripts', array($this, 'assets_queue') );

		Theme::register( "Child", $this );

	}

	/**
	 * Setup the child theme
	 */
	public function setup() {
		// Adds BrowserSync support
		add_theme_support( 'browsersync' );

		// Adds Boostrap themes theme support
		add_theme_support( 'bootstrap-themes' );
	}

	/**
	 * Frontend assets queue
	 */
	public function assets_queue() {
		wp_enqueue_style( 'bootstrap-4' );
		wp_enqueue_style( 'owl-carousel-2' );

		wp_enqueue_script( 'bootstrap-4' );
		wp_enqueue_script( 'owl-carousel-2' );

		add_action( 'wp_head', array($this->theme->Setup, 'default_inline_stylesheet'), 9999 );
		add_action( 'wp_footer', array($this->theme->Setup, 'default_inline_script'), 9999 );
	}

	/**
	 * Frontend site logo
	 *
	 * @see get_custom_logo()
	 *
	 * @param string $html
	 */
	public function site_logo( $html ) {
		if ( $html && ! is_customize_preview() )
			return $html;

		$custom_logo_attr = array(
			'class' => 'custom-logo',
			'alt' => get_bloginfo( 'name', 'display' )
		);

		$custom_logo = sprintf(
			"<span class=\"%1\$s\" itemprop=\"logo\">%2\$s</span>",
			$custom_logo_attr['class'],
			$custom_logo_attr['alt']
		);

		$html = sprintf(
			"<a href=\"%1\$s\" class=\"%2\$s\" rel=\"home\" itemprop=\"url\" title=\"%3\$s\">\n\t%4\$s\n</a>\n",
			esc_url( home_url( '/' ) ),
			'navbar-brand custom-logo-link',
			$custom_logo_attr['alt'],
			$custom_logo
		);

		return $html;
	}

	/**
	 * WooCommerce modifications
	 */
	public function shop_wc_mods() {
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	}

	/**
	 * Gets the list of available css framework themes through the API and caches them
	 *
	 * @global array $bst
	 * @return null|array void|$bst
	 */
	public function bst_get() {
		$bst_key = md5( $this->bs_ver );
		$bst_cache = get_transient( 'bst' );

		if ( $bst_cache && is_array( $bst_cache ) ) {
			$this->bst = $bst_cache;

			return $this->bst;
		}

		$bst_contents = @file_get_contents( 'https://bootswatch.com/api/4.json' );

		if (! $bst_contents)
			return null;

		$bst_contents = json_decode( $bst_contents, true );

		if ( ! ( $bst_contents && is_array( $bst_contents ) ) )
			return null;

		$this->bst = array();

		$replace_ver = false;

		if ( $bst_contents['version'] !== self::BS_VERSION )
			$replace_ver = true;

		foreach ( $bst_contents['themes'] as $theme ) {
			$theme_slug = sanitize_title( $theme['name'] );
			$theme_css = $theme['cssCdn'];

			if ( $replace_ver )
				$theme_css = str_replace(
					$bst_contents['version'], self::BS_VERSION, $theme_css
				);

			$this->bst[$theme_slug] = array(
				'name' => esc_attr( $theme['name'] ),
				'description' => esc_attr( $theme['description'] ),
				'thumbnail' => esc_url( $theme['thumbnail'] ),
				'css' => esc_url( $theme_css )
			);
		}

		if ( $this->bst ) {
			// 1 week cache
			set_transient( 'bst', $this->bst, self::BTS_CACHE_TIMEOUT );

			return $this->bst;
		}

		return null;
	}

	/**
	 * Adds css framework themes selection to existent theme options
	 *
	 * @see /theme/Options->settings()
	 *
	 * @param object $options - /theme/Options
	 * @return void
	 */
	public function bst_options( $options ) {
		if ( ! $this->bst )
			$this->bst = $this->bst_get();

		if ( $this->bst ) {
			$bst_options = array( '' => __( '- Select -', 'theme' ) );

			foreach ( $this->bst as $theme_slug => $theme )
				$bst_options[$theme_slug] = $theme['name'];
		} else {
			return;
		}

		$bst_description = sprintf(
			__( 'Bootstrap free themes from %s', 'theme' ),
			'<a href="https://bootswatch.com" target="_blank">Bootswatch</a>'
		);

		add_settings_field(
			'bst_selected',
			__( 'Select a Bootstrap theme', 'theme' ),
			array($options, 'select_field_render'),
			$options->options_page,
			'theme_settings_frontend_section',
			array(
				'name' => $options->get_name('bst_selected'),
				'value' => $options->get_value('bst_selected'),
				'options' => $bst_options,
				'description' => $bst_description
			)
		);

		$options->add_settings_field_sanitize(
			'bst_selected',
			'string'
		);
	}

	/**
	 * Adds css framework themes selection to existent theme customizer
	 *
	 * @see /WP_Customizer_Manager->wp_loaded()
	 *
	 * @param object $wp_customize - WP_Customize_Manager
	 * @return void
	 */
	public function bst_customizer( $wp_customize ) {
		if ( ! $this->bst )
			$this->bst = $this->bst_get();

		if ( $this->bst ) {
			$bst_options = array( '' => __( '- Select -', 'theme' ) );

			foreach ( $this->bst as $theme_slug => $theme )
				$bst_options[$theme_slug] = $theme['name'];
		} else {
			return;
		}

		$bst_description = sprintf(
			__( 'Bootstrap free themes from %s', 'theme' ),
			'<a href="https://bootswatch.com" target="_blank">Bootswatch</a>'
		);

		$wp_customize->add_setting( 'theme_settings[bst_selected]', array(
			'default' => get_option( 'theme_settings[bst_selected]' ),
			'type' => 'option',
			'capability' => 'manage_options',
			'transport' => 'postMessage'
		) );

		$wp_customize->add_control( 'theme_settings[bst_selected]', array(
			'label' => __( 'Select a Bootstrap theme', 'theme' ),
			'section' => 'view_settings',
			'description' => $bst_description,
			'type' => 'select',
			'choices' => $bst_options
		) );

		$wp_customize->selective_refresh->add_partial( 'theme_settings[bst_selected]', array(
			'settings' => array('theme_settings[bst_selected]'),
			'selector' => '#bootswatch-theme-css',
			'render_callback' => array($this, 'bst_selective_refresh_partial'),
			'container_inclusive' => true
		) );
	}

	/**
	 * Adds the eventually selected css framework theme to the stylesheet queue
	 *
	 * @return void
	 */
	public function bst_queue() {
		wp_dequeue_style( 'bootstrap-4' );

		$bst_selected = $this->bst_get_theme();

		if ( ! $bst_selected )
			return;

		wp_enqueue_style(
			'bootswatch-theme',
			$bst_selected['css'],
			null,
			$this->bs_ver
		);

		// stores the list of available themes in a js variable
		if ( is_customize_preview() ) {
			wp_localize_script(
				'bootswatch-theme',
				'bootswatch_themes',
				$this->bst
			);
		} else {
			
		}
	}

	/**
	 * Gets a theme from the list of available css framework themes or the current selected
	 *
	 * @param string $theme
	 * @return array|bool void
	 */
	public function bst_get_theme( $theme = '' ) {
		if ( ! $theme )
			$theme = $this->theme->Options->get_value( 'bst_selected' );

		if ( $theme ) {
			if ( ! $this->bst )
				$this->bst = $this->bst_get();

			if ( $this->bst && isset( $this->bst[$theme] ) )
				return $this->bst[$theme];
		}

		return false;
	}

	/**
	 * (Re-)renderizes the link tag for css framework theme
	 */
	public function bst_selective_refresh_partial() {
		$bst_selected = $this->bst_get_theme();

		if ( ! $bst_selected )
			$bst_selected = array('name' => '', 'value' => '', 'description' => '', 'css' => '');

		printf(
			"<link rel='stylesheet' id='bootswatch-theme-css'  href='%s' type='text/css' media='all' />\n",
			$bst_selected['css']
		);
	}


}

new Child;