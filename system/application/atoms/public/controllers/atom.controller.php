<?php

class Atom_Controller extends Application_Controller
{
	public $__paths;
	public $__javascripts			= array();
	public $__styles				= array();
	
	public function __construct(RouterRequest $request)
	{
		$this->__style('common');
		$this->__javascript('jquery', 'interface');
		
		$res		= Paths_Config::$base . Dispatcher::$folder_resources . '/' . Application_Config::$folder_resources . '/';
		$res_atom	= $res . Atom_Config::$folder_resources . '/';
		
		$this->__paths = array
		(
			'resources'		=> $res,
			'files'			=> $res . Application_Config::$folder_files . '/',
			'styles'		=> $res_atom . Atom_Config::$folder_styles . '/',
			'javascripts'	=> $res_atom . Atom_Config::$folder_javascripts . '/',
		);
		
		parent::__construct($request);
	}
}

?>
