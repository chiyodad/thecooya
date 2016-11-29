<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'wp_orm.php';

if ( ! class_exists( 'Abstract_Controller' ) ) :

abstract class Abstract_Controller
{
    static private $DEBUG = true;

    protected $prefix;

    protected $component_id;

    protected $option_prefix = '';

    private $dir;

    private $file;

    public $version;

    function __construct($file)
    {
        global $wp_version;

        $this->file = $file;
        $this->dir = dirname($file);
        if ($this->version === null)
            $this->version = $wp_version;
        if ($this->prefix === null)
            $this->prefix = $this->component_id . '_';

        self::debug('Initializing, ' . $this->prefix);
    }

    public function set_loader($loader)
    {
        $this->loader = $loader;
    }

    static function debug($message = '')
    {
        if (self::$DEBUG) {
            // echo '[' . get_called_class() . '] ' . $message . "\n";
        }
    }

    public function add_action($tag, $priority = 10, $accepted_args = 1, $func = null)
    {
        if ($func === null)
            $func = $tag;
        add_action($this->get_name($tag), array($this, $func), $priority, $accepted_args);
        self::debug('Added action: ' . $this->prefix . $func);
    }

    public function action($tag)
    {
        echo $this->get_name($tag);
    }

    private function get_name($tag)
    {
        return $this->prefix . $tag;
    }

    public function create_nonce($tag)
    {
        return wp_create_nonce($this->get_name($tag));
    }

    public function nonce_action_field($tag)
    {
        wp_nonce_field($this->get_name($tag));
        echo '<input type="hidden" name="action" value="' . $this->get_name($tag) . '">';
    }

    public function wp_add_filter($tag, $priority = 10, $accepted_args = 1, $func = null)
    {
        if ($func === null)
            $func = $tag;
        add_filter($tag, array($this, $func), $priority, $accepted_args);
    }

    public function add_filter($tag, $priority = 10, $accepted_args = 1, $func = null)
    {
        if ($func === null)
            $func = $tag;
        add_filter($this->get_name($tag), array($this, $func), $priority, $accepted_args);
    }

    public function remove_filter($tag)
    {
        remove_filter($this->get_name($tag), array($this, $tag));
    }

    public function add_shortcode($tag, $func = null)
    {
        if ($func === null)
            $func = $tag;
        add_shortcode($this->get_name($tag), array($this, $func));
    }

    public function add_ajax($tag, $nopriv = FALSE)
    {
        add_action('wp_ajax_' . ($nopriv ? 'nopriv_' : '' ) . $this->get_name($tag), array($this, 'do_ajax'));
        $this->add_action($tag, 10, 0);
    }

    public function add_ajax_nopriv($tag)
    {
        $this->add_ajax($tag, TRUE);
    }

    public function do_ajax()
    {
        $action = $_REQUEST['action'];
        if ( !isset($_REQUEST['_wpnonce']) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) )
	        $this->ajax_failure('잘못된 경로로 접근하였습니다.');

        try {
            do_action($action);
        } catch (Exception $e) {
            $this->ajax_failure($e->getMessage(), $e->getCode());
        }
    }

	public function ajax_failure($message, $code = 0) {
		$this->ajax_result($code,$message);
	}

	public function ajax_success($message = null, $data = null) {
		$this->ajax_result(200,$message,$data);
	}

	public function ajax_result($code, $message, $data = null) {
		$meta = array('code'=>$code);
		if ($message != null)
			$meta['message'] = $message;

		$result = array('meta'=>$meta);
		if ($data != null)
			$result['data'] = $data;

		$this->result(json_encode($result));
	}

	public function result($message) {
		echo $message;
		die();
	}

	public function view($view_name, $context = array())
    {
        self::debug('view: '.$view_name);
        $context['component'] = $this;
		extract($context, EXTR_OVERWRITE);
		include $this->dir . '/views/' . $view_name . '.php';
	}

	public function view_contents($view_name, $context = array()) {
        $context['component'] = $this;
        ob_start();
		extract($context, EXTR_OVERWRITE);
		include $this->dir . '/views/' . $view_name . '.php';
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
	}

    public function model($model_name)
    {
        $this->import('models/' . $model_name);
    }

    public function import($module)
    {
        require_once $this->dir . '/' . $module . '.php';
    }

    protected function get_request_object($class_name)
    {
        $this->model($class_name);
        $object = new $class_name;
        foreach (get_class_vars($class_name) as $name => $value) {
            if (isset($_REQUEST[$name])) {
                $object->$name = $_REQUEST[$name];
            }
        }
        return $object;
    }

    protected function script($name, $deps = array('jquery'), $local_deps = array())
    {
        foreach($local_deps as $dep) {
            $deps[] = $this->get_name($dep);
        }
        $this->enqueue_script($this->get_name($name), $name, $deps);
    }

    protected function p_script($name, $deps = array('jquery'))
    {
        wp_enqueue_script($name, plugins_url('/assets/js/' . $name . '.js', dirname(__FILE__)), $deps);
        // $this->enqueue_script($name, $name, $deps);
    }

    protected function localize($handle, $name, $data)
    {
        wp_localize_script($this->get_name($handle), $name, $data);
    }

    private function enqueue_script($handle, $src_name, $deps)
    {
        wp_enqueue_script($handle, plugins_url('/assets/js/' . $src_name . '.js', $this->file), $deps);
    }

    public function get_option($option, $default = false)
    {
        return get_option($this->option_prefix . $option, $default);
    }
}

endif;
