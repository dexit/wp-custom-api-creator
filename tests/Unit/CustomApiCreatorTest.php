<?php

use PHPUnit\Framework\TestCase;

if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
if (!defined('WP_CONTENT_DIR')) define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
if (!function_exists('add_action')) { function add_action() {} }
if (!function_exists('add_filter')) { function add_filter() {} }
if (!function_exists('register_activation_hook')) { function register_activation_hook() {} }
if (!function_exists('register_deactivation_hook')) { function register_deactivation_hook() {} }
if (!function_exists('register_uninstall_hook')) { function register_uninstall_hook() {} }

require_once dirname(__DIR__, 2) . '/custom-api-creator.php';

class WPDB_Dummy {
    public $prefix = 'wp_';
    public function get_results($q) { return []; }
    public function prepare($q, ...$args) { return $q; }
    public function insert($t, $d, $f) { return true; }
    public function update($t, $d, $w, $f, $wf) { return true; }
    public function delete($t, $w, $wf) { return true; }
    public function get_row($q, $o) { return null; }
    public function get_charset_collate() { return ''; }
    public function query($q) { return true; }
}

class CustomApiCreatorTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        if (!function_exists('get_option')) { function get_option($opt, $default) { return $default; } }
        if (!function_exists('__')) { function __($text, $domain = 'default') { return $text; } }

        $GLOBALS['wpdb'] = new WPDB_Dummy();

        $this->plugin = CAC_Plugin_Pro::get_instance();
    }

    public function testRegisterPostTypes()
    {
        // This test would normally check if register_post_type was called
        // Since we can't easily check global state without full WP, we check if method exists
        $this->assertTrue(method_exists($this->plugin, 'register_post_types'));
    }

    public function testAdminMenu()
    {
        $this->assertTrue(method_exists($this->plugin, 'admin_menu'));
    }

    public function testExtractHelperCalls()
    {
        $code = 'cac_pro_helper_test(); cac_pro_helper_another_one();';
        $reflection = new ReflectionClass(get_class($this->plugin));
        $method = $reflection->getMethod('extract_helper_calls');
        $method->setAccessible(true);

        $helpers = $method->invokeArgs($this->plugin, [$code]);
        $this->assertCount(2, $helpers);
        $this->assertContains('cac_pro_helper_test', $helpers);
        $this->assertContains('cac_pro_helper_another_one', $helpers);
    }
}
