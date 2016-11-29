<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'abstract_controller.php';

if ( ! class_exists( 'Component_Loader' ) ) :

class Component_Loader
{
    private $dir;

    public $version;

    public function __construct($file)
    {
        $this->dir = dirname($file);
    }

    public function load($name)
    {
		$component = require_once $this->dir . '/' . $name . '/loader.php';
        $component->version = $this->version;
	}
}

endif;

return new Component_Loader(__FILE__);
