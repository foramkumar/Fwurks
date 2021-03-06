<?php

class AutoLoader
{
	protected static $library = array
	(
//		'ClassName'			=> '/subpath/',
		'Template'			=> '/template/',
	);
	
	public static function load($class_name)
	{
		if(isset(self::$library[$class_name]))
		{
			$class_file = Paths_Config::$library . self::$library[$class_name] . $class_name . '.php';
		}
		else if(substr($class_name, -11) == '_Controller')
		{
			$class_file = ($class_name == 'Application_Controller' ? Paths_Config::$app_atoms : Paths_Config::$atom_controllers) . $path . Inflector::to_file($class_name) . '.php';
		}
		else if(substr($class_name, -7) == '_Config')
		{
			$filename = Inflector::to_file($class_name) . '.php';
			
			   file_exists($class_file = Paths_Config::$configs . $filename)
			|| file_exists($class_file = Paths_Config::$app_configs . $filename)
			|| file_exists($class_file = Paths_Config::$atom_configs . $filename)
			|| $class_file = null;
		}
		else
		{
			   file_exists($class_file = Paths_Config::$atom_library . $class_name . '.php')
			|| file_exists($class_file = Paths_Config::$app_models . Inflector::to_file($class_name) . '.model.php')
			|| $class_file = null;
		}
		
		if($class_file){ require_once $class_file; }
	}
}

?>
