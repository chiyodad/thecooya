<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'abstract_component.php';
require_once 'abstract_controller.php';
require_once 'abstract_settings.php';

class Component_Loader
{
    private $dir;

    public function __construct($file)
    {
        $this->dir = dirname($file);
    }

    public function load($name)
    {
		require_once $this->dir . '/' . $name . '/loader.php';
	}
}

return new Component_Loader(__FILE__);
