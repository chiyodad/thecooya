<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Groups_Controller extends Abstract_Controller
{
    protected $component_id = 'groups';

    protected $prefix = '';

    private $group_id;

    function __construct()
    {
        parent::__construct(__FILE__);

		$this->add_action('wpmem_pre_init');
		$this->add_action('dbmem_set_group');
		$this->add_action('user_register');
		$this->add_filter('dbmem_register_fields_arr', 5, 2, 'check_group');
		$this->add_filter('wpmem_register_hidden_fields', 10, 2);
    }

    function user_register( $user_id )
    {
        if (isset($_POST['group_id']) && !empty($_POST['group_id']) && class_exists('Groups_User_Group')) {
            if ( !is_multisite() || is_user_member_of_blog( $user_id ) ) {
                \Groups_User_Group::create(
                    array(
                        'user_id'  => $user_id,
                        'group_id' => $_POST['group_id']
                    )
                );
            }
        }
    }

    function wpmem_register_hidden_fields($hidden, $toggle)
    {
    	if ($this->group_id !== NULL) {
            $hidden .= '<input type="hidden" name="group_id" value="' . $this->group_id . '" />';
        }
        return $hidden;
    }

    function dbmem_set_group($group)
    {
        $this->group_id = $group;
    }

	function wpmem_pre_init() {
		if (defined('GROUPS_CORE_VERSION'))
			$this->pre_wpmem();
	}

	function pre_wpmem() {
		$action =  isset( $_REQUEST['a'] ) ? trim( $_REQUEST['a'] ) :
                (isset($_REQUEST['action']) ? trim( $_REQUEST['action']) : '');
		if (in_array($action, array('register','update','dbmem_register_ajax_register','dbmem_register_ajax_update')) && isset($_REQUEST['group_id']))
			add_filter('option_wpmembers_fields', array($this, 'option_wpmembers_fields'));
	}

	function option_wpmembers_fields($fields) {
        return $this->get_group_fields($fields, $_REQUEST['group_id']);
	}

    function get_group_fields($fields, $group_id = '') {
    	if (empty($group_id))
    		return $fields;

        $group_fields = get_option('dbmembers_group_fields_' . $group_id);
        if (empty($group_fields))
            return $fields;

        $group_fields = explode(',', str_replace(' ', '', $group_fields));
        $new_fields = array();
        foreach( $fields as $field ) {
            if (in_array($field[2],$group_fields))
                $new_fields[] = $field;
        }
        return $new_fields;
	}

	function check_group($fields, $toggle) {
		if (!defined('GROUPS_CORE_VERSION'))
			return $fields;

		if ($toggle === 'edit') {
            $guser = new \Groups_User(get_current_user_id());
            $groups = $guser->groups;
            foreach ($groups as $group) {
            	if ($group->name !== \Groups_Registered::REGISTERED_GROUP_NAME)
            		$this->group_id = $group->group_id;
            }
        }

        return $this->get_group_fields($fields, $this->group_id);
	}

}

return new Groups_Controller;
