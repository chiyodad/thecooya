<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'abstract_component.php';

abstract class Abstract_Controller extends Abstract_Component
{
    private $dir;

    private $url;

    function __construct($file)
    {
        parent::__construct();
        $this->dir = dirname($file) . '/';
        $this->url = plugins_url('/', $file);
    }

    function get_url($path)
    {
        return $this->url . $path;
    }

    function get_path($path)
    {
        return $this->dir . $path;
    }

    public function add_action($tag, $priority = 10, $accepted_args = 1)
    {
        add_action($this->get_name($tag), array($this, $tag), $priority, $accepted_args);
        self::debug('Added action: ' . $this->prefix . $tag);
    }

    public function add_wp_action($tag, $priority = 10, $accepted_args = 1)
    {
        add_action($tag, array($this, $tag), $priority, $accepted_args);
        self::debug('Added action: ' . $tag);
    }

    public function action($tag)
    {
        echo $this->get_name($tag);
    }

    public function nonce_action_field($tag)
    {
        wp_nonce_field($this->get_name($tag));
        echo '<input type="hidden" name="action" value="' . $this->get_name($tag) . '">';
    }

    public function add_filter($tag, $priority = 10, $accepted_args = 1)
    {
        add_filter($this->get_name($tag), array($this, $tag), $priority, $accepted_args);
    }

    public function add_wp_filter($tag, $priority = 10, $accepted_args = 1)
    {
        add_filter($tag, array($this, $tag), $priority, $accepted_args);
    }

    public function add_shortcode($tag)
    {
        add_shortcode($this->get_name($tag), array($this, $tag));
    }

    public function add_ajax($tag, $nopriv = FALSE)
    {
        add_action('wp_ajax_' . ($nopriv ? 'nopriv_' : '' ) . $this->get_name($tag), array($this, 'do_ajax'));
        $this->add_action($tag, 10, 0);
    }

    public function do_ajax()
    {
        $action = $_REQUEST['action'];
        if ( !isset($_REQUEST['_wpnonce']) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], $action ) )
	        $this->ajax_failure('잘못된 경로로 접근하였습니다.');
	        // $this->ajax_failure('Cheating huh?');

        try {
            do_action($action);
        } catch (Exception $e) {
            $this->ajax_failure($e->getMessage(), $e->getCode());
        }
    }

    public function create_nonce($action)
    {
        return wp_create_nonce($this->get_name($action));
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
		include $this->dir . 'views/' . $view_name . '.php';
	}

	public function view_contents($view_name, $context = array()) {
        $context['component'] = $this;
        ob_start();
		extract($context, EXTR_OVERWRITE);
		include $this->dir . 'views/' . $view_name . '.php';
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
        require_once $this->dir . $module . '.php';
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
        wp_enqueue_script($this->get_name($name), $this->url . 'assets/js/' . $name . '.js', $deps);
    }

    protected function script_p($name, $deps = array('jquery'))
    {
        wp_enqueue_script($name, plugins_url('/assets/js/' . $name . '.js', __DIR__), $deps);
    }

    protected function localize($handle, $name, $data)
    {
        wp_localize_script($this->get_name($handle), $name, $data);
    }
}
