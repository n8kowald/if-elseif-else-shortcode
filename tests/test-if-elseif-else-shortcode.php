<?php

/**
 * Class Test_If_Shortcode
 */
class Test_If_Shortcode extends WP_UnitTestCase {

	/**
	 * Setup test conditions
	 */
	public function setUp(): void {

		// Add allowed callables; used for testing - test functions are defined at the bottom of this class.
		add_filter( 'if_elseif_else_shortcode_allowed_callables', function ( $callables ) {
			return array_merge( $callables, [
				'func_is_true',
				'func_is_false',
				'func_is_zero_cool',
				'WpTestUtil::is_true',
				'WpTestUtil::is_zero_cool',
				'WpTestUtil::is_hackers_character',
			] );
		} );
	}

	public function test_iee_get_allowed_callables_filter() {
		$allowed_callables = iee_get_allowed_callables();
		add_filter( 'if_elseif_else_shortcode_allowed_callables', function ( $callables ) {
			$callables[] = 'func_to_add';

			return $callables;
		} );
		$updated_callables = iee_get_allowed_callables();
		$this->assertEquals( $updated_callables, array_merge( $allowed_callables, [ 'func_to_add' ] ) );
	}

	public function test_iee_is_valid_callable_false() {
		$result = iee_is_valid_callable( 'turtles' );
		$this->assertEquals( $result, false );
	}

	public function test_iee_is_valid_callable_true() {
		$result = iee_is_valid_callable( 'func_is_true' );
		$this->assertEquals( $result, true );
	}

	// Static class method call (As of PHP 5.2.3)
	public function test_iee_is_valid_callable_static_class_method_simple() {
		$result = iee_is_valid_callable( 'WpTestUtil::is_true' );
		$this->assertEquals( $result, true );
	}

	public function test_if_true() {
		$content = '[if func_is_true]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_false() {
		$content = '[if func_is_false]unexpected[/if]expected';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_with_param_true() {
		$content = '[if func_is_zero_cool zero-cool]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_with_param_false() {
		$content = '[if func_is_zero_cool the-plague]unexpected[else]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_with_param_with_static_method() {
		$content = '[if WpTestUtil::is_true]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_not_callable() {
		$content = '[if doesnotexist]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'If shortcode error: [if] argument must be callable' );
	}

	public function test_else() {
		$content = '[if func_is_false]unexpected[else]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_if_elseif() {
		$content = '[if func_is_true]expected[elseif func_is_false]unexpected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif() {
		$content = '[if func_is_false]unexpected[elseif func_is_true]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_with_no_else() {
		$content = '[if func_is_false]unexpected[elseif func_is_true]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_not_callable() {
		$content = '[if func_is_false]unexpected[elseif doesnotexist]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'If shortcode error: [elseif] argument must be callable' );
	}

	public function test_elseif_with_param() {
		$content = '[if func_is_false]unexpected[elseif func_is_zero_cool zero-cool]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_with_multiple_params() {
		$content = '[if func_is_false]unexpected[elseif WpTestUtil::is_hackers_character kevin-mitnick zero-cool]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_is_valid_callable_static_class_method() {
		$content = '[if func_is_false]unexpected[elseif WpTestUtil::is_true]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_is_valid_callable_static_class_method_with_param() {
		$content = '[if func_is_false]unexpected[elseif WpTestUtil::is_zero_cool zero-cool]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_else() {
		$content = '[if func_is_false]unexpected[elseif func_is_false]unexpected[else]expected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_elseif_else() {
		$content = '[if func_is_false]unexpected[elseif func_is_false]unexpected[elseif func_is_true]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}

	public function test_elseif_elseif_elseif_else() {
		$content = '[if func_is_false]unexpected[elseif func_is_false]unexpected[elseif func_is_false]unexpected[elseif func_is_true]expected[else]unexpected[/if]';
		$result  = do_shortcode( $content );
		$this->assertEquals( $result, 'expected' );
	}
}

/* This is gross but works for boolean function testing */
/**
 * @return bool
 */
function func_is_true() {
	return true;
}

/**
 * @return bool
 */
function func_is_false() {
	return false;
}

/**
 * @param string $name
 *
 * @return bool
 */
function func_is_zero_cool( $name ) {
	return $name == 'zero-cool';
}

class WpTestUtil {
	public static function is_true() {
		return true;
	}

	public function is_valid() {
		return true;
	}

	public static function is_zero_cool( $name ) {
		return $name == 'zero-cool';
	}

	public static function is_hackers_character( $name1, $name2 ) {
		$hackers_characters = [ 'zero-cool', 'the-plague' ];

		return in_array( $name1, $hackers_characters ) || in_array( $name2, $hackers_characters );
	}
}
