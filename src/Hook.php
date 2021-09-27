<?php

namespace Neoassist\ProtocolExport;

/**
 * Class Hook
 * @package Neoassist\ProtocolExport
 */
class Hook
{

    /**
     * @var Hook
     */
    private static $instance;

    /**
     * events handlers
     *
     * @var array
     */
    private $hooks = [];

    /**
     * construct
     */
    private function __construct()
    {
    }

    /**
     * clone
     */
    private function __clone()
    {
    }

    /**
     * @param string $hook_name
     * @param callable $fn
     * @return void
     */
    public static function add(string $hook_name, callable $fn)
    {
        $instance = self::get_instance();
        $instance->hooks[$hook_name][] = $fn;
    }

    /**
     * @param string $hook_name
     * @param [mixed] $params
     * @return void
     */
    public static function fire(string $hook_name, $params = null)
    {
        $instance = self::get_instance();
        if (isset($instance->hooks[$hook_name])) {
            foreach ($instance->hooks[$hook_name] as $fn) {
                call_user_func_array($fn, array(&$params));
            }
        }
    }

    /**
     * @param string $hook_name
     * @return void
     */
    public static function remove($hook_name)
    {
        $instance = self::get_instance();
        unset($instance->hooks[$hook_name]);
        var_dump($instance->hooks);
    }

    /**
     * @return Hook
     */
    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new Hook();
        }
        return self::$instance;
    }
}
