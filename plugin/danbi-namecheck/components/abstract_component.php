<?php
namespace Danbi\Namecheck;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'wp_orm.php';

abstract class Abstract_Component
{
    // static private $DEBUG = true;
    static private $DEBUG = FALSE;

    protected $prefix;

    protected $id;

    function __construct()
    {
        $this->prefix = $this->id . '_';
        self::debug('Initializing, ' . $this->prefix);
    }

    static function debug($message = '')
    {
        if (self::$DEBUG) {
            echo '[' . get_called_class() . '] ' . $message . "\n";
        }
    }

    protected function get_name($tag)
    {
        return $this->prefix . $tag;
    }

}
