<?php
/*
Plugin Name: Kint Debugger
Plugin URI: http://upthemes.com/plugins/kint-debugger/
Description: A simple WordPress wrapper for Kint, a debugging tool to output information about variables and traces. Kint Debugger integrates seamlessly with the Debug Bar plugin.
Version: 0.3
Author: Brian Fegter
Author URI: http://upthemes.com
License: MIT
*/

define( 'KINT_DIR', dirname( __FILE__ ) . '/' );
require KINT_DIR . 'config.default.php';

if ( is_readable( KINT_DIR . 'config.php' ) ) {
	require KINT_DIR . 'config.php';
}

class Kint
{
	const VERSION = '0.32';

	// todo what was I thinking?! redo configuration storage
	private static $pathDisplayCallback;
	private static $traceCleanupCallback;
	private static $colorCodeLoops;
	private static $hideSequentialKeys;
	private static $maxStrLength;
	private static $maxLevels;
	private static $enabled;
	private static $skin;
	private static $charset;
	private static $devel;


	private static $_firstRun = TRUE;
	private static $_collapsed;

	// make calls to Kint::dump() from different places in source coloured differently.
	// I don't think this needs more colors, do you? Perhaps I should randomly generate some when these are depleted..
	private static $_dumpColors = array(
		'#c0d4df',
		'#FFFFFF',
		'#C0C0C0',
		'#808080',
		'#000000',
		'#FF0000',
		'#800000',
		'#FFFF00',
		'#00FF00',
		'#008080',
		'#0000FF',
		'#FF00FF',
		'#00FFFF',
		'#808000',
		'#008000',
		'#800080',
		'#000080',
		'#e3ecf0',
	);
	private static $_usedColors = array();

	/**
	 * getter/setter for the enabled parameter, called at the beginning of every public function as getter, also
	 * initializes the settings if te first time it's run.
	 *
	 * @static
	 * @param null $value
	 * @return void|bool
	 */
	public static function enabled( $value = NULL )
	{
		// act both as a setter...
		if ( func_num_args() > 0 ) {
			self::$enabled = $value;
			return;
		}

		// ...and a getter
		return self::$enabled;
	}

	public static function _init()
	{
		// init settings
		if ( isset( $GLOBALS['_kint_settings'] ) ) {
			foreach ( $GLOBALS['_kint_settings'] as $key => $val ) {
				self::$$key = $val;
			}
		}

	}

	/**
	 * Prints a debug backtrace
	 * @static
	 * @param array $trace null
	 * @return void
	 */
	public static function trace( $trace = NULL )
	{
		if ( !Kint::enabled() ) return;

		echo self::_css();

		isset( $trace ) or $trace = debug_backtrace( true );

		// Non-standard function calls
		$statements = array( 'include', 'include_once', 'require', 'require_once' );

		$output = array();
		foreach ( $trace as $step )
		{
			self::$traceCleanupCallback and $step = call_user_func( self::$traceCleanupCallback, $step );

			// if the user defined trace cleanup function returns null, skip this line
			if ( $step === null ) {
				continue;
			}

			if ( !isset( $step['function'] ) ) {
				// Invalid trace step
				continue;
			}

			if ( isset( $step['file'] ) AND isset( $step['line'] ) ) {
				// Include the source of this step
				$source = self::_debugSource( $step['file'], $step['line'] );
			}

			if ( isset( $step['file'] ) ) {
				$file = $step['file'];

				if ( isset( $step['line'] ) ) {
					$line = $step['line'];
				}
			}

			// function()
			$function = $step['function'];

			if ( in_array( $step['function'], $statements ) ) {
				if ( empty( $step['args'] ) ) {
					// No arguments
					$args = array();
				} else {
					// Sanitize the file path
					$args = array( self::_debugPath( $step['args'][0] ) );
				}
			}
			elseif ( isset( $step['args'] ) )
			{

				if ( empty($step['class']) && !function_exists( $step['function'] ) ) {
					// Introspection on closures or language constructs in a stack trace is impossible
					$params = NULL;
				} else {
					if ( isset( $step['class'] ) ) {
						if ( method_exists( $step['class'], $step['function'] ) ) {
							$reflection = new ReflectionMethod( $step['class'], $step['function'] );
						} else {
							$reflection = new ReflectionMethod( $step['class'], '__call' );
						}
					} else {
						$reflection = new ReflectionFunction( $step['function'] );
					}

					// Get the function parameters
					$params = $reflection->getParameters();
				}

				$args = array();

				foreach ( $step['args'] as $i => $arg )
				{
					if ( isset( $params[$i] ) ) {
						// Assign the argument by the parameter name
						$args[$params[$i]->name] = $arg;
					} else {
						// Assign the argument by number
						$args[$i] = $arg;
					}
				}
			}

			if ( isset( $step['class'] ) ) {
				// Class->method() or Class::method()
				$function = $step['class'] . $step['type'] . $step['function'];
			}

			if ( isset( $step['object'] ) ) {
				$function = $step['class'] . $step['type'] . $step['function'];
			}

			$output[] = array(
				'function' => $function,
				'args'     => isset( $args ) ? $args : null,
				'file'     => isset( $file ) ? $file : null,
				'line'     => isset( $line ) ? $line : null,
				'source'   => isset( $source ) ? $source : null,
				'object'   => isset( $step['object'] ) ? $step['object'] : null,
			);

			unset( $function, $args, $file, $line, $source );
		}
		ob_start('kint_debug_globals');
		include KINT_DIR . 'view/trace.phtml';
		ob_end_flush();
	}

	/**
	 * Dump information about a variable
	 *
	 * @param mixed $data
	 * @return void|string
	 */
	public static function dump($data)
	{
		if ( !Kint::enabled() ) return;


		// decide what action to take based on parameter count
		if ( func_num_args() === 0 ) {

			// todo if no arguments were provided, dump the whole environment
			// self::env(); // todo
			return;
		}

		// find caller information
		$prevCaller = array();
		$trace = debug_backtrace();
		while ( $callee = array_pop( $trace ) ) {
			if ( strtolower( $callee['function'] ) === 'd' ||
			     strtolower( $callee['function'] ) === 'dd' ||
			     isset( $callee['class'] ) && strtolower( $callee['class'] ) === strtolower( __CLASS__ )
			) {
				break;
			} else {
				$prevCaller = $callee;
			}
		}


		list( $names, $modifier ) = self::_getPassedNames( $callee, '' );

		// catches @, + and -
		switch ( $modifier ) {
			case '-':
				self::$_firstRun = TRUE;
				ob_clean();
				break;
			case '+':
				$prevLevels = self::$maxLevels;
				self::$maxLevels = 0;
				break;
		}


		$ret = self::_css()
				. self::_wrapStart( $callee );

		foreach ( func_get_args() as $k => $argument ) {
			$dump = self::_dump( $argument );
			list( $class, $plus ) = self::_collapsed();
			$ret .= "<dl><dt{$class}>{$plus}" . ( !empty( $names[$k] ) ? "<dfn>{$names[$k]}</dfn> "
					: "" ) . "{$dump}</dl>";
		}
		$ret .= self::_wrapEnd( $callee, $prevCaller );

		self::$_firstRun = FALSE;


		if ( $modifier === '+' ) {
			self::$maxLevels = $prevLevels;
		}

		if ( $modifier === '@' ) {
			self::$_firstRun = TRUE;
			return $ret;
		}
		ob_start('kint_debug_globals');
		echo $ret;
		ob_end_flush();
	}


	/**
	 * elements are:
	 *
	 * div.kint - root of one call to function
	 * dl - root of one element
	 * dt - short info, may be expandable if followed by dd
	 * dd - collapsed infrmation about a variable
	 * pre - whole string output
	 * var - element name
	 * dfn - elemnt type
	 * span - element size
	 * div.root>ul>li:last - version and callee info
	 *
	 *
	 * @param $var
	 * @param int $level
	 * @return string
	 */
	private static function _dump( &$var, $level = 0 )
	{

		// initialize function names into variables for prettier string output (html and implode are also DRY)
		$html     = "htmlspecialchars";
		$implode  = "implode";
		$strlen   = "strlen";
		$count    = "count";
		$getClass = "get_class";


		if ( $var === NULL ) {
			return '<var>NULL</var>';
		} elseif ( is_bool( $var ) )
		{
			return '<var>bool</var> ' . ( $var ? 'TRUE' : 'FALSE' );
		}
		elseif ( is_float( $var ) )
		{
			return '<var>float</var> ' . $var;
		}
		elseif ( is_int( $var ) )
		{
			return '<var>integer</var> ' . $var;
		}
		elseif ( is_resource( $var ) )
		{
			if ( ( $type = get_resource_type( $var ) ) === 'stream' AND $meta = stream_get_meta_data( $var ) ) {

				if ( isset( $meta['uri'] ) ) {
					$file = $meta['uri'];

					if ( function_exists( 'stream_is_local' ) ) {
						// Only exists on PHP >= 5.2.4
						if ( stream_is_local( $file ) ) {
							$file = call_user_func( self::$pathDisplayCallback, $file );
						}
					}

					return "<var>resource</var><span> ({$type})</span> {$html($file,0)}";
				}
				else
				{
					return "<var>resource</var><span> ({$type})</span>";
				}
			}
			else
			{
				return "<var>resource</var><span> ({$type})</span>";
			}
		}
		elseif ( is_string( $var ) )
		{
			if ( strlen( $var ) > self::$maxStrLength ) {

				// encode and truncate
				$str = htmlspecialchars( substr( self::_stripWhitespace( $var ), 0, self::$maxStrLength ), ENT_NOQUOTES ) . '';

				self::_collapsed( TRUE );
				return "<var>string</var> ({$strlen($var)}) \"{$str}&nbsp;&hellip;\"</dt><dd><pre>{$html($var,0)}</pre></dd>";
			}
			else
			{
				return "<var>string</var>({$strlen($var)}) \"{$html($var,0)}\"";
			}
		}
		elseif ( is_array( $var ) )
		{
			$output = array();

			static $marker;

			if ( $marker === NULL ) {
				// Make a unique marker
				$marker = uniqid( "\x00" );
			}

			if ( empty( $var ) ) {
				return "<var>array</var><span>(0)</span>";
			}
			elseif ( isset( $var[$marker] ) )
			{
				$output[] = "<dt>(*RECURSION*)</dt>";
			}
			elseif ( self::$maxLevels === 0 || $level < self::$maxLevels )
			{
				$isSeq = self::_isSequential( $var );

				$var[$marker] = TRUE;

				foreach ( $var as $key => & $val )
				{
					if ( $key === $marker ) continue;

					$key = $isSeq ? "" : "'<dfn>{$html($key,0)}</dfn>' =>";


					$dump = self::_dump( $val, $level + 1 );

					list( $class, $plus ) = self::_collapsed();
					$output[] = "<dt{$class}>{$plus}{$key} {$dump}";
				}

				unset( $var[$marker] );
			}
			else
			{
				$output[] = "<dt>(depth too great)</dt>";
			}

			self::_collapsed( TRUE );
			return "<var>array</var> <span>({$count($var)})</span><dd><dl>{$implode($output,'')}</dl></dd>";
		}
		elseif ( is_object( $var ) )
		{
			// Copy the object as an array
			$array = (array) $var;

			$output = array();

			$hash = spl_object_hash( $var );

			// Objects that are being dumped
			static $objects = array();

			if ( empty( $array ) ) {
				return "<var>object {$getClass($var)} </var><span>{0}</span>";
			}
			elseif ( isset( $objects[$hash] ) )
			{
				$output[] = "<dt>{*RECURSION*}</dt>";
			}
			elseif ( ( self::$maxLevels === 0 ) || $level < self::$maxLevels )
			{
				$objects[$hash] = TRUE;

				$reflection = new ReflectionClass( $var );
				foreach ( $reflection->getProperties( ReflectionProperty::IS_STATIC ) as $property ) {
					if ( $property->isPrivate() ) {
						$property->setAccessible( true );
						$access = "private";
					} elseif ($property->isProtected()) {
						$property->setAccessible( true );
						$access = "protected";
					} else {
						$access = 'public';
					}
					$access = "<var>" . $access . " static</var>";
					$key = $property->getName();

					$value = $property->getValue();
					$dump = self::_dump( $value, $level + 1 );
					list( $class, $plus ) = self::_collapsed();
					$output[] = "<dt{$class}>{$plus}{$access} '<dfn>{$key}</dfn>' :: {$dump}";
				}

				foreach ( $array as $key => & $val )
				{
					if ( $key[0] === "\x00" ) {

						$access = "<var>" . ( $key[1] === "*" ? "protected" : "private" ) . "</var>";

						// Remove the access level from the variable name
						$key = substr( $key, strrpos( $key, "\x00" ) + 1 );
					}
					else
					{
						$access = "<var>public</var>";
					}
					$dump = self::_dump( $val, $level + 1 );
					list( $class, $plus ) = self::_collapsed();

					$output[] = "<dt{$class}>{$plus}{$access} <dfn>{$key}</dfn> -> {$dump}";
				}
				unset( $objects[$hash] );

			}
			else
			{
				// Depth too great
				$output[] = "<dt>{depth too great}</dt>";
			}

			self::_collapsed( TRUE );
			return "<var>object {$getClass($var)}</var> <span>{{$count($array)}}</span><dd><dl>{$implode($output,'')}</dl></dd>";
		}
		else // should never happen
		{
			return '<var>' . gettype( $var ) . '</var> ' . htmlspecialchars( var_export( $var, true ), ENT_NOQUOTES );
		}
	}

	private static function _css()
	{
		if ( !self::$_firstRun ) return '';
		self::$_firstRun = FALSE;
		$ret = '';

		if ( self::$charset ) {
			$ret .= '<head><meta charset="' . self::$charset . '"></head>';
		}

		// load uncompressed sources if in devel mode
		$ret .= '<script>';
		$ret .= file_get_contents( KINT_DIR . 'view/' . ( self::$devel ? 'src/' : '' ) . 'kint.js' );
		$ret .= '</script>';

		$ret .= '<style>';
		$ret .= file_get_contents( KINT_DIR . 'view/' . ( self::$devel ? 'src/' : '' ) . self::$skin );
		$ret .= '</style>';

		return $ret;
	}


	private static function _debugPath( $file, $line = NULL )
	{
		if ( !$line ) { // called from resource dump
			return $file;
		}
		return "<u>" . $file . "</u> line <i>{$line}</i>";
	}

	/**
	 * returns whether the array is numeric and in sequence starting from zero (that means indices are not important)
	 * @static
	 * @param array $array
	 * @return bool
	 */
	private static function _isSequential( array $array )
	{
		return self::$hideSequentialKeys
				? array_keys( $array ) === range( 0, count( $array ) - 1 )
				: false;
	}

	private static function _debugSource( $file, $line_number, $padding = 7 )
	{
		if ( !$file OR !is_readable( $file ) ) {
			// Continuing will cause errors
			return FALSE;
		}

		// Open the file and set the line position
		$file = fopen( $file, 'r' );
		$line = 0;

		// Set the reading range
		$range = array(
			'start' => $line_number - $padding,
			'end'   => $line_number + $padding
		);

		// Set the zero-padding amount for line numbers
		$format = '% ' . strlen( $range['end'] ) . 'd';

		$source = '';
		while ( ( $row = fgets( $file ) ) !== FALSE )
		{
			// Increment the line number
			if ( ++$line > $range['end'] ) {
				break;
			}

			if ( $line >= $range['start'] ) {
				// Make the row safe for output
				$row = htmlspecialchars( $row, ENT_NOQUOTES );

				// Trim whitespace and sanitize the row
				$row = '<span>' . sprintf( $format, $line ) . '</span> ' . $row;

				if ( $line === $line_number ) {
					// Apply highlighting to this row
					$row = '<div class="kint-highlight">' . $row . '</div>';
				}
				else
				{
					$row = '<div>' . $row . '</div>';
				}

				// Add to the captured source
				$source .= $row;
			}
		}

		// Close the file
		fclose( $file );

		return '<pre class="source">' . $source . '</pre>';
	}

	/**
	 * called with TRUE when a dumped variable wants to be displayed collapsed, called each time
	 * w/o parameters before displaying a variable - basically a method to communicate with the callee
	 *
	 * @static
	 * @param bool $bool
	 * @param $extraClass string
	 * @return string
	 */
	private static function _collapsed( $bool = FALSE, $extraClass = '' )
	{
		$class   = '';
		$element = '';
		if ( self::$_collapsed ) {

			$element = '<div class="kint-plus _kint-collapse"></div>';
			$class   = ' class="kint-parent"';
		}
		self::$_collapsed = $bool;

		return array( $class, $element );
	}

	private static function _wrapStart( $callee )
	{
		if ( self::$colorCodeLoops && isset( $callee['file'] ) ) {
			$uid = crc32( $callee['file'] . $callee['line'] );

			if ( isset( self::$_usedColors[$uid] ) ) {
				$color = self::$_usedColors[$uid];
			} else {
				$color = array_pop( self::$_dumpColors );
				self::$_usedColors[$uid] = $color
						? $color
						: '#fff';
			}

			$style = " style=\"box-shadow: 6px 0 3px -3px {$color} inset\"";
		} else {
			$style = '';
		}

		return "<div class=\"kint\"{$style}>";
	}

	private static function _wrapEnd( $callee, $prevCaller )
	{
		$callingFunction = '';
		if ( isset( $prevCaller['class'] ) ) {
			$callingFunction = $prevCaller['class'];
		}
		if ( isset( $prevCaller['type'] ) ) {
			$callingFunction .= $prevCaller['type'];
		}
		if ( isset( $prevCaller['function'] ) ) {
			$callingFunction .= $prevCaller['function'] . '()';
		}
		$callingFunction and $callingFunction = " in ({$callingFunction})";


		$calleeInfo = isset( $callee['file'] )
				? 'Called from ' . call_user_func( self::$pathDisplayCallback, $callee['file'], $callee['line'] ) . $callingFunction
				: '';


		return "<span>{$calleeInfo}</span></div>";
	}

	private static function _getPassedNames( $callee, $defaultName = 'literal' )
	{
		if ( !isset( $callee['file'] ) || !is_readable( $callee['file'] ) ) {
			return FALSE;
		}

		// open the file and read it up to the position where the function call expression ended
		$file   = fopen( $callee['file'], 'r' );
		$line   = 0;
		$source = '';
		while ( ( $row = fgets( $file ) ) !== FALSE )
		{
			if ( ++$line > $callee['line'] ) break;
			$source .= $row;
		}
		fclose( $file );
		$source = self::_removePhpComments( $source );
		$source = str_replace( array( "\r", "\n" ), ' ', $source );


		$codePattern = empty( $callee['class'] )
				? $callee['function']
				: $callee['class'] . $callee['type'] . $callee['function'];
		// get the position of the last call to the function
		preg_match_all( "#[\\s:](\\+|-|!|@)?{$codePattern}\\s*(\\()#i", $source, $matches, PREG_OFFSET_CAPTURE );
		$match    = end( $matches[2] );
		$modifier = end( $matches[1] );
		$modifier = $modifier[0];


		$passedParameters = substr( $source, $match[1] + 1 );
		// we now have a string like this:
		// <parameters passed>); <the rest of the last read line>


		// remove everything in brackets and quotes, we don't need nested statements nor literal strings which would
		// only complicate separating individual arguments
		$c          = strlen( $passedParameters );
		$inString   = $escaped = FALSE;
		$i          = 0;
		$inBrackets = 0;
		while ( $i < $c ) {
			$letter = $passedParameters[$i];
			if ( $inString === FALSE ) {
				if ( $letter === '\'' || $letter === '"' ) {
					$inString = $letter;
				} elseif ( $letter === '(' ) {
					$inBrackets++;
				} elseif ( $letter === ')' ) {
					$inBrackets--;
					if ( $inBrackets === -1 ) { //this means we are out of the brackets that denote passed parameters
						$passedParameters = substr( $passedParameters, 0, $i );
						break;
					}
				}
			} elseif ( $letter === $inString && !$escaped ) {
				$inString = FALSE;
			}

			// place an untype-able character instead of whatever was inside quotes or brackets, we don't
			// need that info. We'll later replace it with '...'
			if ( $inBrackets > 0 ) {
				if ( $inBrackets > 1 || $letter !== '(' ) {
					$passedParameters[$i] = "\x07";
				}
			}
			if ( $inString !== FALSE ) {
				if ( $letter !== $inString || $escaped ) {
					$passedParameters[$i] = "\x07";
				}
			}

			$escaped = ( $letter === '\\' );
			$i++;
		}

		// by now we have an unnested arguments list, lets make it to an array for processing further
		$_ = explode( ',', preg_replace( "#\x07+#", '...', $passedParameters ) );

		// test each argument whether it was passed literrary or was it an expression or a variable name
		$expressions = array();
		foreach ( $_ as $argument ) {
			if ( strpos( $argument, '$' ) !== FALSE ||
			     strpos( $argument, 'new' ) !== FALSE ||
			     strpos( $argument, '=' ) !== FALSE ||
			     ( strpos( $argument, '(' ) !== FALSE && !preg_match( '#\s*array\s*#', $argument ) )
			) {
				$expressions[] = trim( $argument );
			}
			else
			{
				$expressions[] = $defaultName;
			}
		}

		return array( $expressions, $modifier );
	}

	/**
	 * as advertised :)
	 *
	 * @static
	 * @param  $passedParameters
	 * @return string
	 */
	private static function _removePhpComments( $passedParameters )
	{
		$newStr = '';
		$tokens = token_get_all( $passedParameters );

		$commentTokens = array( T_COMMENT );
		if ( defined( 'T_DOC_COMMENT' ) ) {
			$commentTokens[] = constant( 'T_DOC_COMMENT' );
		}
		if ( defined( 'T_ML_COMMENT' ) ) {
			$commentTokens[] = constant( 'T_ML_COMMENT' );
		}

		foreach ( $tokens as $token ) {
			if ( is_array( $token ) ) {
				if ( in_array( $token[0], $commentTokens ) ) continue;

				$token = $token[1];
			}

			$newStr .= $token;
		}
		return $newStr;

	}

	private static function _stripWhitespace( $string )
	{
		$search = array(
			'#[ \t]+[\r\n]#' => "", // leading whitespace after line end
			'#[\n\r]+#'      => "\n", // multiple newlines
			'# {2,}#'        => " ", // multiple spaces
			'#\t{2,}#'       => "\t", // multiple tabs
			'#\t | \t#'      => "\t", // tabs and spaces together
		);
		return preg_replace( array_keys( $search ), $search, trim( $string ) );
	}
}

/**
 * Alias of {@link kint::dump()}
 *
 * @param mixed $data,...
 *
 * @see kint::dump()
 */
if ( !function_exists( 'd' ) ) {
	function d()
	{
		if ( !Kint::enabled() ) return null;

		$args = func_get_args();
		return call_user_func_array( array( 'Kint', 'dump' ), $args );
	}

	function dd()
	{
		if ( !Kint::enabled() ) return;

		$args = func_get_args();
		call_user_func_array( array( 'Kint', 'dump' ), $args );
		die;
	}
}

if ( !function_exists( 's' ) ) {
	function s()
	{
		if ( !Kint::enabled() ) return;

		$argv = func_get_args();
		ob_start('kint_debug_globals');
		echo '<pre>';
		foreach ( $argv as $k => $v ) {
			$k && print( "\n\n" );
			echo kintLite( $v );
		}
		echo '</pre>';
		ob_end_flush();
	}

	function sd()
	{
		if ( !Kint::enabled() ) return;

		echo '<pre>';
		foreach ( func_get_args() as $k => $v ) {
			$k && print( "\n\n" );
			echo kintLite( $v );
		}
		echo '</pre>';
		die;

	}

	/**
	 * sadly not DRY yet
	 *
	 * @param $var
	 * @param int $level
	 * @return string
	 */
	function kintLite(&$var, $level = 0)
	{

		// initialize function names into variables for prettier string output (html and implode are also DRY)
		$html = "htmlspecialchars";
		$implode = "implode";
		$strlen = "strlen";
		$count = "count";
		$getClass = "get_class";


		if ( $var === NULL ) {
			return 'NULL';
		}
		elseif ( is_bool( $var ) )
		{
			return 'bool ' . ( $var ? 'TRUE' : 'FALSE' );
		}
		elseif ( is_bool( $var ) )
		{
			return 'bool ' . ( $var ? 'TRUE' : 'FALSE' );
		}
		elseif ( is_float( $var ) )
		{
			return 'float ' . $var;
		}
		elseif ( is_int( $var ) )
		{
			return 'integer ' . $var;
		}
		elseif ( is_resource( $var ) )
		{
			if ( ( $type = get_resource_type( $var ) ) === 'stream' AND $meta = stream_get_meta_data( $var ) ) {

				if ( isset( $meta['uri'] ) ) {
					$file = $meta['uri'];

					return "resource ({$type}) {$html($file,0)}";
				}
				else
				{
					return "resource ({$type})";
				}
			}
			else
			{
				return "resource ({$type})";
			}
		}
		elseif ( is_string( $var ) )
		{
			return "string ({$strlen($var)}) \"{$html($var)}\"";
		}
		elseif ( is_array( $var ) )
		{
			$output = array();
			$space = str_repeat( $s = '    ', $level );

			static $marker;

			if ( $marker === NULL ) {
				// Make a unique marker
				$marker = uniqid( "\x00" );
			}

			if ( empty( $var ) ) {
				return "array (0)";
			}
			elseif ( isset( $var[$marker] ) )
			{
				$output[] = "(\n$space$s*RECURSION*\n$space)";
			}
			elseif ( $level < 5 )
			{
				$isSeq = array_keys( $var ) === range( 0, count( $var ) - 1 );

				$output[] = "(";

				$var[$marker] = TRUE;


				foreach ( $var as $key => &$val )
				{
					if ( $key === $marker ) continue;

					$key = $space . $s . ( $isSeq ? "" : "'{$html($key,0)}' =>" );

					$dump = kintLite( $val, $level + 1 );
					$output[] = "{$key} {$dump}";
				}

				unset( $var[$marker] );
				$output[] = "$space)";

			}
			else
			{
				$output[] = "(\n$space$s*depth too great*\n$space)";
			}
			return "array ({$count($var)}) {$implode("\n", $output)}";
		}
		elseif ( is_object( $var ) )
		{
			// Copy the object as an array
			$array = (array)$var;

			$output = array();
			$space = str_repeat( $s = '    ', $level );

			$hash = spl_object_hash( $var );

			// Objects that are being dumped
			static $objects = array();

			if ( empty( $array ) ) {
				return "object {$getClass($var)} {0}";
			}
			elseif ( isset( $objects[$hash] ) )
			{
				$output[] = "{\n$space$s*RECURSION*\n$space}";
			}
			elseif ( $level < 5 )
			{
				$output[] = "{";
				$objects[$hash] = TRUE;

				$reflection = new ReflectionClass( $var );
				foreach ( $reflection->getProperties( ReflectionProperty::IS_STATIC ) as $property ) {
					if ( $property->isPrivate() ) {
						$property->setAccessible( true );
						$access = "private";
					} elseif ($property->isProtected()) {
						$property->setAccessible( true );
						$access = "protected";
					} else {
						$access = 'public';
					}
					$access = $access . " static";
					$key = $property->getName();

					$value = $property->getValue();
					$output[] = "$space$s{$access} {$key} :: " . kintLite( $value, $level + 1 );
				}

				foreach ( $array as $key => & $val )
				{
					if ( $key[0] === "\x00" ) {

						$access = $key[1] === "*" ? "protected" : "private";

						// Remove the access level from the variable name
						$key = substr( $key, strrpos( $key, "\x00" ) + 1 );
					}
					else
					{
						$access = "public";
					}

					$output[] = "$space$s$access $key -> " . kintLite( $val, $level + 1 );
				}
				unset( $objects[$hash] );
				$output[] = "$space}";

			}
			else
			{
				$output[] = "{\n$space$s*depth too great*\n$space}";
			}

			return "object {$getClass($var)} ({$count($array)}) {$implode("\n", $output)}";
		}
		else
		{
			return gettype( $var ) . htmlspecialchars( var_export( $var, true ), ENT_NOQUOTES );
		}
	}
}

if ( !function_exists( 'dump_wp_query' ) ) {
	function dump_wp_query(){
		global $wp_query;
		ob_start('kint_debug_globals');
		d($wp_query);
		ob_end_flush();
	}
}

if ( !function_exists( 'dump_wp' ) ) {
	function dump_wp(){
		global $wp;
		ob_start('kint_debug_globals');
		d($wp);
		ob_end_flush();
	}
}
if ( !function_exists( 'dump_post' ) ) {
	function dump_post(){
		global $post;
		ob_start('kint_debug_globals');
		d($post);
		ob_end_flush();
	}
}

function kint_debug_globals($buffer){
	global $kint_debug;
	$kint_debug[] = $buffer;
	if(class_exists('Debug_Bar')) return;
	return $buffer;
}

class kintDebugBarPanel {
	function title() {
		return __('Kint Debugger');
	}
      
	function prerender() {
		
	}
      
	function is_visible() {
		return true;
	}
      
	function render() {
		global $kint_debug;
		if(is_array($kint_debug)){
			foreach($kint_debug as $line)
				echo $line;
		}
	}
}

function kint_debug_bar_panel($panels) {
  $panels[] = new kintDebugBarPanel;
  return $panels;
}
add_filter('debug_bar_panels', 'kint_debug_bar_panel');

Kint::_init();
