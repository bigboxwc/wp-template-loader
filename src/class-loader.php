<?php
/**
 * WordPress Template Loader.
 *
 * Help organize WordPress theme structure by separating and
 * providing easier ways to load views.
 *
 * @package BigBoxWC\WP_Template_Loader
 */

namespace BigBoxWC\WP_Template_Loader;

/**
 * Class Loader
 *
 * @since 1.0.0
 *
 * @package BigBoxWC\WP_Template_Loader
 */
class Loader {

	/**
	 * View path relative to the theme root.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $base_path;

	/**
	 * "Layout" directory relative to `$base_path`.
	 *
	 * All standard WordPress template files should be located here.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $layout_dir;

	/**
	 * "Part/ials" directory relative to `$base_path`.
	 *
	 * Files that would be used with `get_template_part()` should be located here.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected static $partial_dir;

	/**
	 * Instantiate a Loader object and map config.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Configuration array.
	 */
	public function __construct( $args = [] ) {
		$defaults = [
			'base_path'   => 'resources/views',
			'layout_dir'  => 'layout',
			'partial_dir' => 'partials',
		];

		$args = wp_parse_args( $args, $defaults );

		self::$base_path   = $args['base_path'];
		self::$layout_dir  = $args['layout_dir'];
		self::$partial_dir = $args['partial_dir'];
	}

	/**
	 * Watch include paths and adjust loading location.
	 *
	 * @since 1.0.0
	 */
	public static function watch() {
		// @see wp-includes/template.php line 41.
		$types = [
			'index',
			'404',
			'archive',
			'author',
			'category',
			'tag',
			'taxonomy',
			'date',
			'embed',
			'home',
			'frontpage',
			'page',
			'paged',
			'search',
			'single',
			'singular',
			'attachment',
		];

		foreach ( $types as $type ) {

			/**
			 * Filter the template hierarchy.
			 *
			 * Required to do this now instead of on `{$type}_template` because
			 * this does not happen until after `locate_template()` is run.
			 *
			 * @since 1.0.0
			 *
			 * @param array $templates The current templates being looked for.
			 * @return array $templates The adjusted template directories.
			 */
			add_filter(
				"{$type}_template_hierarchy", function( $templates ) {
					$_templates = [];

					foreach ( $templates as $k => $template ) {
						// Skip the parent theme's /index.php which is required to be a valid theme.
						if ( ! wp_get_theme()->parent() && 'index.php' === $template ) {
							unset( $templates[ $k ] );
						} elseif ( wp_get_theme()->parent() && 'index.php' === $template && ! file_exists( get_stylesheet_directory() . '/index.php' ) ) {  // Skip the child's /index.php only if it does not exist.
							unset( $templates[ $k ] );
						}

						$_templates[] = trailingslashit( self::$layout_dir ) . $template;
					}

					// Merge with original. This allows child themes to avoid /resources/views/ structure.
					$templates = array_merge( $templates, $_templates );

					return $templates;
				}
			);

		} // End foreach().
	}

	/**
	 * Render a view.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array   $templates The name of the template.
	 * @param array          $args Variables to pass to partial.
	 * @param (false|string) $path Optional view base path.
	 */
	public static function view( $templates, $args = [], $path = false ) {
		echo self::get_view( $templates, $args, $path ); // WPCS: XSS okay.
	}

	/**
	 * Return a view.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array   $templates The name of the template.
	 * @param array          $args Variables to pass to partial.
	 * @param (false|string) $path Optional view base path.
	 * @return string
	 */
	public static function get_view( $templates, $args = [], $path = false ) {
		if ( ! is_array( $templates ) ) {
			$templates = [ $templates ];
		}

		// Extract variable to use in template file.
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		$_templates = [];

		if ( ! $path ) {
			$path = self::$base_path;
		}

		foreach ( $templates as $key => $template_name ) {
			$_templates[] = $template_name . '.php';
			$_templates[] = trailingslashit( $path ) . $template_name . '.php';
		}

		$template = locate_template( $_templates );

		ob_start();

		if ( $template ) {
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * Render a partial.
	 *
	 * This serves mainly as an alias for `BigBoxWC\WP_Template_Loader\Loader::view()` but always looks
	 * in the `$partial_dir` directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $partial The file name of the partial to load.
	 * @param array  $args Variables to pass to partial.
	 */
	public static function partial( $partial, $args = [] ) {
		echo self::get_partial( $partial, $args ); // XSS: ok.
	}

	/**
	 * Return a partial.
	 *
	 * This serves mainly as an alias for `BigBoxWC\WP_Template_Loader\Loader::get_view()` but always looks
	 * in the `$partial_dir` directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $partial The file name of the partial to load.
	 * @param array  $args Variables to pass to partial.
	 * @return string
	 */
	public static function get_partial( $partial, $args = [] ) {
		ob_start();

		self::view( trailingslashit( self::$partial_dir ) . $partial, $args );

		return ob_get_clean();
	}

}
