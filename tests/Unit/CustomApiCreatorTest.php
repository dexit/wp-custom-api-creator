<?php

use PHPUnit\Framework\TestCase;

class CustomApiCreatorTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        $this->plugin = new CAC_Plugin_Class();
    }

    public function testRegisterCustomPostType()
    {
        $this->plugin->register_custom_post_type();
        $this->assertTrue(post_type_exists('cac_plugin'));
    }

    public function testLoadTextdomain()
    {
        $this->plugin->load_textdomain();
        $this->assertTrue(is_textdomain_loaded('cac-plugin-creator'));
    }

    public function testAddAdminMenu()
    {
        global $menu;
        $this->plugin->add_admin_menu();
        $this->assertNotEmpty($menu);
    }

    public function testEnqueueAdminScripts()
    {
        global $hook_suffix;
        $hook_suffix = 'post.php';
        $this->plugin->enqueue_admin_scripts($hook_suffix);
        $this->assertTrue(wp_script_is('cac-plugin-admin', 'enqueued'));
    }
}
