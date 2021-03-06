<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( file_exists( $_tests_dir . '../wordpress/wp-content/themes/twentyseventeen/style.css' ) ) {

	class Twenty_Seventeen_Test extends PLL_UnitTestCase {
		static $stylesheet;

		static function wpSetUpBeforeClass() {
			parent::wpSetUpBeforeClass();

			self::create_language( 'en_US' );
			self::create_language( 'fr_FR' );

			require_once PLL_INC . '/api.php';
			$GLOBALS['polylang'] = &self::$polylang;

			self::$stylesheet = get_option( 'stylesheet' ); // save default theme
			switch_theme( 'twentyseventeen' );
		}

		function setUp() {
			parent::setUp();

			require_once get_template_directory() . '/functions.php';
		}

		static function wpTearDownAfterClass() {
			parent::wpTearDownAfterClass();

			unset( $GLOBALS['polylang'] );
			switch_theme( self::$stylesheet );
		}

		function test_front_page_panels() {
			$en = self::factory()->post->create( array( 'post_title' => 'section 1 EN' ) );
			self::$polylang->model->post->set_language( $en, 'en' );

			$fr = self::factory()->post->create( array( 'post_title' => 'section 1 FR' ) );
			self::$polylang->model->post->set_language( $fr, 'fr' );

			self::$polylang->model->post->save_translations( $en, compact( 'en', 'fr' ) );

			set_theme_mod( 'panel_1', $en );

			self::$polylang = new PLL_Frontend( self::$polylang->links_model );
			self::$polylang->init();
			do_action( 'pll_init' );
			PLL_Plugins_Compat::instance()->twenty_seventeen_init(); // Called manually as the constructor of PLL_Plugins_Compat is called before activation of Twenty Seventeen

			self::$polylang->curlang = self::$polylang->model->get_language( 'fr' ); // brute force

			ob_start();
			twentyseventeen_front_page_section( null, 1 );
			$this->assertNotFalse( strpos( ob_get_clean(), 'section 1 FR' ) );

			self::$polylang->curlang = self::$polylang->model->get_language( 'en' ); // brute force

			ob_start();
			twentyseventeen_front_page_section( null, 1 );
			$this->assertNotFalse( strpos( ob_get_clean(), 'section 1 EN' ) );
		}
	}

} // file_exists
