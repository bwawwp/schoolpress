<?php
isset($GLOBALS['_kint_settings']) or $GLOBALS['_kint_settings'] = array();
$_kintSettings = &$GLOBALS['_kint_settings'];



/** @var bool if set to false, kint will become silent, same as Kint::enabled(false) */
$_kintSettings['enabled'] = true;


/**
 * @var callback
 *
 * @param string $file filename where the function was called
 * @param int|null $line the line number in the file (not applicable when used in resource dumps)
 */
$_kintSettings['pathDisplayCallback'] = "kint::_debugPath";


/**
 * @var callback|null
 *
 * @param array $step each step of the backtrace is passed to this callback to clean it up or skip it entirely
 * @return array|null you can return null if you want to bypass outputting this step
 */
$_kintSettings['traceCleanupCallback'] = null;


/** @var int max length of string before it is truncated and displayed separately in full */
$_kintSettings['maxStrLength'] = 60;


/** @var bool whether to add a right colored gutter based on the location of the call to the dump */
$_kintSettings['colorCodeLoops'] = true;


/** @var int max array/object levels to go deep, if zero no limits are applied */
$_kintSettings['maxLevels'] = 5;


/** @var bool whether dumped indexed arrays that are in ideal sequence are displayed */
$_kintSettings['hideSequentialKeys'] = true;


/** @var string the css file to format the output of kint */
$_kintSettings['skin'] = 'kint.css';


/** @var string|null if set, prepends a <head> tag with appropriate meta charset value (issue 7) */
$_kintSettings['charset'] = null;


/** @var bool only set to true if you want to develop kint and know what you're doing */
$_kintSettings['devel'] = false;

unset($_kintSettings);