<?php

use PHPUnit\Framework\TestCase;

class CustomApiCreatorTest extends TestCase
{
    protected $plugin;

    protected function setUp(): void
    {
        $this->plugin = new CAC_Plugin_Pro();
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

    public function testRegisterPostTypes()
    {
        $this->plugin->register_post_types();
        $this->assertTrue(post_type_exists('cac_api'));
        $this->assertTrue(taxonomy_exists('cac_api_group'));
    }

    public function testAdminMenu()
    {
        global $menu;
        $this->plugin->admin_menu();
        $this->assertNotEmpty($menu);
    }

    public function testAdminAssets()
    {
        global $hook_suffix;
        $hook_suffix = 'post.php';
        $this->plugin->admin_assets($hook_suffix);
        $this->assertTrue(wp_script_is('cac-pro-admin', 'enqueued'));
    }

    public function testAddMetaBoxes()
    {
        global $wp_meta_boxes;
        $this->plugin->add_meta_boxes();
        $this->assertArrayHasKey('cac_api', $wp_meta_boxes);
    }

    public function testSaveMetaBoxes()
    {
        $post_id = $this->factory->post->create();
        $_POST['cac_pro_meta_box_nonce'] = wp_create_nonce('cac_pro_meta_box');
        $_POST['cac_pro_endpoint'] = 'test-endpoint';
        $_POST['cac_pro_methods'] = ['GET'];
        $_POST['cac_pro_access'] = 'public';
        $_POST['cac_pro_roles'] = ['administrator'];
        $_POST['cac_pro_cache'] = 3600;
        $_POST['cac_pro_group'] = 'test-group';
        $_POST['behavior_selection'] = 'new';
        $_POST['cac_pro_code'] = 'return true;';
        $_POST['cac_pro_params'] = json_encode(['param1' => 'value1']);
        $_POST['cac_pro_response'] = json_encode(['response' => 'value']);

        $this->plugin->save_meta_boxes($post_id);

        $this->assertEquals('test-endpoint', get_post_meta($post_id, '_cac_pro_endpoint', true));
        $this->assertEquals(['GET'], get_post_meta($post_id, '_cac_pro_methods', true));
        $this->assertEquals('public', get_post_meta($post_id, '_cac_pro_access', true));
        $this->assertEquals(['administrator'], get_post_meta($post_id, '_cac_pro_roles', true));
        $this->assertEquals(3600, get_post_meta($post_id, '_cac_pro_cache', true));
        $this->assertEquals('test-group', wp_get_post_terms($post_id, 'cac_api_group', ['fields' => 'slugs'])[0]);
        $this->assertEquals('new', get_post_meta($post_id, '_cac_pro_behavior', true));
        $this->assertEquals('return true;', get_post_meta($post_id, '_cac_pro_code', true));
        $this->assertEquals(['param1' => 'value1'], get_post_meta($post_id, '_cac_pro_params', true));
        $this->assertEquals(['response' => 'value'], get_post_meta($post_id, '_cac_pro_response', true));
    }

    public function testRegisterApiEndpoints()
    {
        $this->plugin->register_api_endpoints();
        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/cac-pro/v1', $routes);
    }

    public function testRegisterPluginEndpoints()
    {
        $this->plugin->register_plugin_endpoints();
        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/cac-pro/v1/helpers', $routes);
        $this->assertArrayHasKey('/cac-pro/v1/endpoints', $routes);
    }

    public function testGetHelpersList()
    {
        $request = new WP_REST_Request('GET', '/cac-pro/v1/helpers');
        $response = $this->plugin->get_helpers_list($request);
        $this->assertNotEmpty($response);
    }

    public function testGetHelperCode()
    {
        $request = new WP_REST_Request('GET', '/cac-pro/v1/helpers/get_post_data');
        $response = $this->plugin->get_helper_code($request);
        $this->assertNotEmpty($response);
    }

    public function testGetEndpointsList()
    {
        $request = new WP_REST_Request('GET', '/cac-pro/v1/endpoints');
        $response = $this->plugin->get_endpoints_list($request);
        $this->assertNotEmpty($response);
    }

    public function testGetEndpointDetails()
    {
        $post_id = $this->factory->post->create(['post_type' => 'cac_api']);
        $request = new WP_REST_Request('GET', '/cac-pro/v1/endpoints/' . $post_id);
        $response = $this->plugin->get_endpoint_details($request);
        $this->assertNotEmpty($response);
    }

    public function testCheckAdminPermission()
    {
        $this->assertTrue($this->plugin->check_admin_permission());
    }

    public function testActivate()
    {
        $this->plugin->activate();
        global $wpdb;
        $table_name = $wpdb->prefix . 'cac_helpers';
        $this->assertEquals($table_name, $wpdb->get_var("SHOW TABLES LIKE '$table_name'"));
        $this->assertNotEmpty(get_option('cac_pro_settings'));
        $this->assertNotEmpty(get_option('cac_pro_default_behavior'));
    }

    public function testDeactivate()
    {
        $this->plugin->deactivate();
        // Add assertions for deactivation if needed
    }

    public function testUninstall()
    {
        $this->plugin->uninstall();
        global $wpdb;
        $table_name = $wpdb->prefix . 'cac_helpers';
        $this->assertNull($wpdb->get_var("SHOW TABLES LIKE '$table_name'"));
        $this->assertFalse(get_option('cac_pro_settings'));
        $this->assertFalse(get_option('cac_pro_default_behavior'));
    }
}
