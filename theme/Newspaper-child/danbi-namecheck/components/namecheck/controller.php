<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'settings.php';

class Namecheck_Controller extends Abstract_Controller
{
    protected $id = 'namecheck';

    protected $settings;

    function __construct()
    {
        parent::__construct(__FILE__);
        $this->settings = new Namecheck_Settings($this->id);
    }
}

return new Namecheck_Controller;
