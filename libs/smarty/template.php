<?php
require_once __DIR__.'/Smarty.class.php';

class Template extends Smarty 
{
	public function __construct() 
	{
		parent::__construct();
        $this->setTemplateDir('./views');
        $this->setCompileDir('./views/cache');
        $this->caching = 0;
        $this->auto_literal = TRUE; 
        $this->left_delimiter = "{"; 
        $this->right_delimiter = "}";
	}
}
