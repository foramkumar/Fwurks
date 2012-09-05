<?php

require getcwd() . '/' . Dispatcher::$folder_system . DIRECTORY_SEPARATOR . Dispatcher::$folder_configs . DIRECTORY_SEPARATOR .'Paths.config.php';

require Paths_Config::$configs		. 'System.config.php';
require Paths_Config::$app_configs	. 'application.config.php';
require Paths_Config::$library		. 'Inflector.php';
require Paths_Config::$library		. 'Router.php';
require Paths_Config::$library		. 'BaseController.php';
require Paths_Config::$library		. 'AutoLoader.php';
require Paths_Config::$app_atoms	. 'ApplicationController.php';

spl_autoload_register('library\AutoLoader::load', true, true);


print_r(get_class_vars(Database_Config));

require Paths_Config::$library . 'database' . DIRECTORY_SEPARATOR . 'ActiveRecord' . DIRECTORY_SEPARATOR . 'ActiveRecord.php';
ActiveRecord\Config::initialize(function($config)
{
//	$cfg->set_model_directory('/path/to/your/model_directory');
	$config->set_connections(Database_Config::$connections);
	$config->set_default_connection(Database_Config::$default_connection);
});

// removing notices
isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] = '/';


/**
 * Prints debug information for a variable
 *
 * @param mixed $var varialbe to print
 * @param string $label label of the variable
 * @return $var
 */
function d($var = null, $label = null, $return = false, $backtrace = false)
{
	$type = gettype($var);
	$regexps = array
	(
		'/\[([\w\d\?]*)(:[\w\d\?]+)?(?>:(protected|private))\] =>/u' => '[<strong style="color: #069;">\\1</strong> <span style="color: #666;">\\3</span>\\2] =>',
		'/\[([\w\d\?]*)] =>/u' => '[<strong style="color: #069;">\\1</strong>] =>',
	);
	$dump = preg_replace(array_keys($regexps), array_values($regexps), print_r($var, true));
	$dump = "<strong style='font-size: 15px; line-height: 40px;'>Debug <em>($type)</em>: $label</strong>\n$dump\n";
	
	$trace = '';
	if($backtrace)
	{
		$backtrace = debug_backtrace(false);
		$trace = '<table border="0" style="font-size: 11px; border-collapse: collapse;">';
		foreach($backtrace as $bt)
		{
			$class 		= isset($bt['class']) 		? $bt['class'] 		: '';
			$type 		= isset($bt['type']) 		? $bt['type'] 		: '';
			$function 	= isset($bt['function']) 	? $bt['function'] 	: '';
			$file	 	= isset($bt['file']) 		? $bt['file'] 		: '';
			$line	 	= isset($bt['line']) 		? $bt['line'] 		: '';
			$trace .= "<tr><td style='padding-right: 20px;'>{$class}{$type}{$function}()</td><td>".substr($file, strlen(getcwd())+1).":{$line}</td></tr>";
		}
		$trace .= '</table>';
		
		$trace = "<strong style='font-size: 15px; line-height: 40px;'>Debug Trace: $label</strong> \n$trace";
	}
	
	$dump = '<pre style="font-size: 13px; font-family: Verdana, sans-serif; background: #F5F5F5;">'. $trace . $dump .'</pre><hr />';
	
	if(!$return){ echo $dump; }
	return !$return ? $var : $dump;
}

/**
 * Prints debug information for a variable and exits
 *
 * @param mixed $var varialbe to print
 * @param string $label label of the variable
 */
function de($var = null, $label = null, $backtrace = true){ d($var, $label, false, $backtrace); exit; }

//function df($var, $file = 'log')
//{
//	file_put_contents(SystemConfig::$filesPath.'temp/'.$file.'.log', print_r($var, 1)."\n\n");
//	chmod(SystemConfig::$filesPath.'temp/'.$file.'.log', 0777);
//}


function bench($text, callable $func, $iterations = 10000)
{
	$start = microtime(1);
	
	for($i = $iterations; $i; $i--){ $func(); }
	
	$time = explode('.', number_format((microtime(1) - $start)/$iterations, 50));
	$time = implode(' ', str_split($time[0], 3)) . '.' . substr(implode(' ', str_split($time[1], 3)), 0, 15);
	
	
	echo $time, ': ', $text, '<br />'; 
}

?>
