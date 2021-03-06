<?php
/**
 * @In the name of God!
 * @author: Mohammad Sadegh Dehghan Niri (MSDN)
 * @email: info@apadanacms.ir
 * @link: http://www.apadanacms.ir
 * @license: http://www.gnu.org/licenses/
 * @copyright: Copyright © 2012-2015 ApadanaCms.ir. All rights reserved.
 * @Apadana CMS is a Free Software
 */

defined('security') or exit('Direct Access to this location is not allowed.');

function _error_handler($severity, $message, $filepath, $line)
{
	$is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

	// When a fatal error occurred, set the status header to '500 Internal Server Error'
	if ($is_error)
	{
		header('Status: 500 Internal Server Error',true);
	}

	// Should we ignore the error? We'll get the current error_reporting
	// level and add its bits with the severity bits to find out.
	if (($severity & error_reporting()) !== $severity)
	{
		return;
	}

	//apadana_log($severity, $message, $filepath, $line);

	// Should we display the error?
	if (ini_get('display_errors'))
	{
		show_php_error($severity, $message, $filepath, $line);
	}

	// If the error is fatal, the execution of the script should be stopped because
	// errors can't be recovered from. Halting the script conforms with PHP's
	// default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
	if ($is_error)
	{
		exit(); // EXIT_ERROR
	}
}

function show_php_error($severity, $message, $filepath, $line)
{
	global $ob_level;

	static $have_error;

	$error_levels = array(
		E_STRICT		=>	'Runtime Notice',
		E_ERROR			=>	'Error',
		E_WARNING		=>	'Warning',
		E_PARSE			=>	'Parsing Error',
		E_NOTICE		=>	'Notice',
		E_CORE_ERROR	=>	'Core Error',
		E_CORE_WARNING	=>	'Core Warning',
		E_COMPILE_ERROR	=>	'Compile Error',
		E_COMPILE_WARNING 	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_DEPRECATED 		=> 'Deprecated'
	);
	$severity = isset($error_levels[$severity]) ? $error_levels[$severity] : $severity;


	$filepath = str_replace('\\', '/', $filepath);
	$filepath = str_replace(root_dir, "", $filepath);

	$itpl = new template('error.tpl', root_dir.'engine/templates/',false);

	if( !isset($have_error) || $have_error != true){
		echo "<style>.apadana_error_main{background:#FFE1E1;border:#FF6A6A 1px solid;padding:5px;margin:5px;direction:ltr;text-align:left}.apadana_error_highlight{background-color: #FFFCFC;border-radius: 4px;padding: 2px;border: 1px solid #AAA;}.trace{background-color: #F7F7F9;border: 1px solid #B9B9D1;border-radius: 4px;padding: 9px 14px;direction:ltr;text-align:left;margin-top: -1px;}.erbacktr{list-style: none;padding: 0;margin: 0;}.erbacktr li:first-child{border-radius: 5px 5px 0 0;}.erbacktr li{border-radius: 0;}.erbacktr li:last-child{border-radius: 0 0 5px 5px;}</style>";
		$have_error = true;
	}
	
	$itpl->assign(array(
		'{severity}' => $severity,
		'{filepath}' => $filepath,
		'{line}' => $line,
		'{message}' => $message
	));

	if (defined('show_debug_backtrace') && show_debug_backtrace === true){
		$itpl->assign(array(
			'[backtrace]' => null,
			'[/backtrace]' => null,
		));
		foreach (debug_backtrace() as $error){
			if( $error['function'] != "show_php_error" && $error['function'] != "_error_handler"){
				$itpl->add_for('bt', array(
					'{bt_line}' => @$error['line'],
					'{bt_file}' => @str_replace(root_dir, "", $error['file']),
					'{bt_func}' => @$error['function']
				));
			}
		}
	}else{
		$itpl->block('#\\[backtrace\\](.*?)\\[/backtrace\\]#s', '');
	}
	$data = $itpl->get_var();
	echo $data;

	unset($itpl);
}

//We Should Tell The PHP We Have Our Own Error And Shutdown Handler.
if(!is_ajax() && !is_cli() )
	set_error_handler('_error_handler');
// register_shutdown_function('_error_handler');