<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'abstract_component.php';

abstract class Abstract_Settings extends Abstract_Component
{
    protected $page = 'general';

    protected $sections = array();

    protected $page_args;

    function __construct($id)
    {
        $this->id = $id;
        parent::__construct();
        add_action('admin_init', array($this, 'init'));
        add_action('admin_menu', array($this, 'menu'));
    }

    abstract function init();

    function menu()
    {
    }

    function add_options_page($args)
    {
        $this->page_args = $args;
        // add_options_page($page_title, $menu_title, $capability, $menu_slug, $function = '' );
        add_options_page($args['page_title'], $args['menu_title'], $args['capability'], $this->page, array($this, 'callback_page'));
    }

    function callback_page()
    {
        ?>
        <div class="wrap">
            <h2><?php echo $this->page_args['page_title']; ?></h2>
            <p class="description"><?php echo $this->page_args['desc']; ?></p>

            <form method="POST" action="options.php">
                <?php
                settings_fields($this->page);
                do_settings_sections($this->page);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    function add_section($section = array())
    {
        // $this->sections[$section['id']] = $section;
        $section_id = $this->get_name($section['id']);
        $section_cb = new Section_Callback($section);
        add_settings_section(
            $section_id,
            $section['title'],
            array($section_cb, 'callback'),
            $this->page
        );

        foreach ($section['fields'] as $field) {
            // $field_cb = new Field_Callback($field, $section_id);
            add_settings_field(
                $this->get_name($field['id']),
                $field['title'],
                array($this, 'callback_field'),
                $this->page,
                $section_id,
                array('field'=>$field, 'section_id'=>$section_id)
            );
        }
        register_setting($this->page, $section_id);
    }

    function callback_field($args)
    {
        extract($args);
        $value = get_option($section_id, array());
        $value = isset($value[$field['id']]) ? $value[$field['id']] : '';
        switch($field['type']) {
            case 'text':
                printf('<input name="%s[%s]" id="%s_%s" type="text" value="%s" class=regular-text ltf" /><p class="description" id="%s_%s-description">%s</p>',
                    $section_id,
                    $field['id'],
                    $section_id,
                    $field['id'],
                    $value,
                    $section_id,
                    $field['id'],
                    $field['desc']
                );
                break;
            default:
                break;
        }
    }
}

class Section_Callback {
    private $section;
    function __construct($section)
    {
        $this->section = $section;
    }
    function callback()
    {
        echo '<p>' . $this->section['desc'] . '</p>';
    }
}
