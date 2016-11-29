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
        $this->add_action('success');
    }

    function success($data)
    {
        if (is_user_logged_in() && get_option('dbmembers_profile_namecheck_save', '0') === '1') {
            $user_id = get_current_user_id();
            foreach ($data as $name => $value) {
                update_user_meta($user_id, 'namecheck_'. strtolower($name), $value);
            }
        }
    }
}

return new Namecheck_Controller;
