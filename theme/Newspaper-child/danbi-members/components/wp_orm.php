<?php
namespace dbmembers;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WP_ORM' ) ) :

class WP_ORM {

    private static function connect($entity) {
        $class = get_class($entity);
        return property_exists($class,'_database') ?
            new wpdb(DB_USER, DB_PASSWORD, $class::$_database, DB_HOST) :
            $GLOBALS['wpdb'];
    }

    public static function insert($entity) {
        $wpdb = self::connect($entity);

        $class = get_class($entity);

        $data = array();
        $column_formats = $class::$_column_formats;

        foreach ( get_object_vars( $entity ) as $key => $value ) {
            $data[$key] = is_array($value) ? serialize($value) : $value;
        }

        $data = array_intersect_key($data, $column_formats);
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        if ($wpdb->insert($class::get_table(), $data) == false)
            $wpdb->print_error();

        return $wpdb->insert_id;
    }

    public static function get($entity) {
        $wpdb = self::connect($entity);

        $class = get_class($entity);

        $select_sql = 'SELECT * FROM ' . $class::get_table();

        $where_sql = 'WHERE 1=1';
        $column_formats = $class::$_column_formats;
        foreach ( get_object_vars( $entity ) as $key => $value )
            if (isset($column_formats[$key]) && !is_null($value))
                $where_sql .=  $wpdb->prepare(' AND ' . $key . '=' . $column_formats[$key], $value);

        $sql = "$select_sql $where_sql";
        $results = $wpdb->get_row($sql, ARRAY_A);
        if (!empty($results))
            $results = new $class($results);

        return $results;
    }

    public static function select($entity, $orderby = '', $conditions = NULL) {
        $wpdb = self::connect($entity);

        $class = get_class($entity);

        $select_sql = 'SELECT * FROM ' . $class::get_table();

        $where_sql = 'WHERE 1=1';
        $column_formats = $class::$_column_formats;
        if ($conditions === NULL) {
            foreach ( get_object_vars( $entity ) as $key => $value )
                if (isset($column_formats[$key]) && !is_null($value))
                    $where_sql .=  ' AND ' . $wpdb->prepare($key . '=' . $column_formats[$key], $value);
        } else {
            foreach ($conditions as $condition) {
                if (is_array($condition)) {
                    $key = $condition[0];
                    $op = $condition[1];
                    $value = $condition[2];
                    if (isset($column_formats[$key]) && !is_null($value))
                        $where_sql .=  ' AND ' . $wpdb->prepare($key . $op . $column_formats[$key], $value);
                } else if (is_string($condition)) {
                    $where_sql .= ' AND ' . $condition;
                }
            }
        }

        $sql = "$select_sql $where_sql";

        if ($orderby !== '')
            $sql .= ' ORDER BY ' . $orderby;

        $results = $wpdb->get_results($sql, ARRAY_A);
        if (!empty($results)) {
            $list = array();
            foreach($results as $row) {
                $list[] = new $class($row);
            }
            $results = $list;
        }

        return $results;
    }

    public static function delete($entity) {
        $wpdb = self::connect($entity);

        $class = get_class($entity);

        $column_formats = $class::$_column_formats;
        $where = array();
        $where_format = array();
        foreach ( get_object_vars( $entity ) as $key => $value )
            if (isset($column_formats[$key]) && !is_null($value)) {
                $where[$key] = $value;
                $where_format[] = $column_formats[$key];
            }

        $wpdb->delete($class::get_table(), $where, $where_format);
    }

    public static function update($entity, $param = null) {
        $wpdb = self::connect($entity);

        $class = get_class($entity);

        $column_formats = $class::$_column_formats;

        $data = array();
        $where = array();
        $format = array();
        $where_format = array();
        if ($param == null) {
            foreach ( get_object_vars( $entity ) as $key => $value ) {
                if (isset($column_formats[$key]) && !is_null($value)) {
                    if ($key == $class::$_primary_key) {
                        $where[$key] = $value;
                        $where_format[] = $column_formats[$key];
                    } else {
                        $data[$key] = is_array($value) ? serialize($value) : $value;
                        $format[] = $column_formats[$key];
                    }
                }
            }
        } else {
            foreach ( get_object_vars( $entity ) as $key => $value ) {
                if (isset($column_formats[$key]) && !is_null($value)) {
                    $data[$key] = is_array($value) ? serialize($value) : $value;
                    $format[] = $column_formats[$key];
                }
            }
            foreach ( $param as $key => $value ) {
                if (isset($column_formats[$key]) && !is_null($value)) {
                    $where[$key] = $value;
                    $where_format[] = $column_formats[$key];
                }
            }
        }

        return $wpdb->update($class::get_table(), $data, $where, $format, $where_format);
    }
}

class WP_Entity {

    // abstract static $_primary_key;

    // abstract static $_column_formats;

    // abstract static $_table_name;

    function __construct($data = array()) {
        foreach($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {
        $this->$property = $value;
        return $value;
    }
}

endif;
