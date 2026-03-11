<?php
/**
 * Plugin Name: Custom API Creator Pro
 * Plugin URI: https://wordpress.org/plugins/custom-api-creator-pro/
 * Description: Create custom API endpoints with advanced features and helper utilities.
 * Version: 2.0.0
 * Author: Mehdi Rezaei
 * Author URI: https://mehd.ir
 * License: GPLv3
 * Text Domain: cac-pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class CAC_Plugin_Pro {
    private static $instance;
    private $helpers = [];
    private $custom_functions = [];
    private $api_cache = [];
    private $log_path;
    private $settings = [];
    private $api_groups = [];
    private $default_behavior;

    public static function get_instance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->log_path = WP_CONTENT_DIR . '/cac-pro-logs/';
        
        // Initialize core functionality
        $this->init_hooks();
        $this->load_helpers();
        $this->load_settings();
        $this->register_default_helpers();
    }

    private function init_hooks() {
        // Core hooks
        add_action('init', [$this, 'register_post_types']);
        add_action('rest_api_init', [$this, 'register_api_endpoints']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_boxes']);
        
        // Custom columns
        add_filter('manage_cac_api_posts_columns', [$this, 'custom_columns']);
        add_action('manage_cac_api_posts_custom_column', [$this, 'custom_column_content'], 10, 2);
        
        // Custom REST endpoints
        add_action('rest_api_init', [$this, 'register_plugin_endpoints']);
    }

    public function register_post_types() {
        // Register API endpoint post type
        register_post_type('cac_api', [
            'labels' => [
                'name' => __('API Endpoints', 'cac-pro'),
                'singular_name' => __('API Endpoint', 'cac-pro')
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options'
            ],
            'map_meta_cap' => true
        ]);

        // Register API group taxonomy
        register_taxonomy('cac_api_group', 'cac_api', [
            'labels' => [
                'name' => __('API Groups', 'cac-pro'),
                'singular_name' => __('API Group', 'cac-pro')
            ],
            'public' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'hierarchical' => true
        ]);
    }

    public function admin_menu() {
        add_menu_page(
            __('Custom API Pro', 'cac-pro'),
            __('Custom API Pro', 'cac-pro'),
            'manage_options',
            'cac-pro',
            [$this, 'admin_page'],
            'dashicons-rest-api',
            80
        );
        
        add_submenu_page(
            'cac-pro',
            __('API Endpoints', 'cac-pro'),
            __('API Endpoints', 'cac-pro'),
            'manage_options',
            'edit.php?post_type=cac_api'
        );
        
        add_submenu_page(
            'cac-pro',
            __('Add New', 'cac-pro'),
            __('Add New', 'cac-pro'),
            'manage_options',
            'post-new.php?post_type=cac_api'
        );
        
        add_submenu_page(
            'cac-pro',
            __('API Groups', 'cac-pro'),
            __('API Groups', 'cac-pro'),
            'manage_options',
            'edit-tags.php?taxonomy=cac_api_group&post_type=cac_api'
        );
        
        add_submenu_page(
            'cac-pro',
            __('Settings', 'cac-pro'),
            __('Settings', 'cac-pro'),
            'manage_options',
            'cac-pro-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'cac-pro',
            __('Helpers', 'cac-pro'),
            __('Helpers', 'cac-pro'),
            'manage_options',
            'cac-pro-helpers',
            [$this, 'helpers_page']
        );
    }

    public function admin_assets($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            $screen = get_current_screen();
            if ('cac_api' === $screen->post_type) {
                wp_enqueue_script('cac-pro-admin', plugin_dir_url(__FILE__) . 'assets/js/admin.js', ['jquery', 'wp-codemirror'], '1.0', true);
                wp_enqueue_style('cac-pro-admin', plugin_dir_url(__FILE__) . 'assets/css/admin.css', [], '1.0');
                wp_enqueue_style('wp-codemirror');
                
                // Localize script with helper functions
                wp_localize_script('cac-pro-admin', 'cacPro', [
                    'helpers' => array_keys($this->helpers),
                    'nonce' => wp_create_nonce('cac-pro-nonce')
                ]);
            }
        }
    }

    public function add_meta_boxes() {
        add_meta_box(
            'cac-pro-endpoint-details',
            __('Endpoint Details', 'cac-pro'),
            [$this, 'endpoint_details_meta_box'],
            'cac_api',
            'normal',
            'high'
        );
        
        add_meta_box(
            'cac-pro-endpoint-code',
            __('Endpoint Code', 'cac-pro'),
            [$this, 'endpoint_code_meta_box'],
            'cac_api',
            'normal',
            'high'
        );
        
        add_meta_box(
            'cac-pro-endpoint-helpers',
            __('Helper Functions', 'cac-pro'),
            [$this, 'helpers_meta_box'],
            'cac_api',
            'side',
            'default'
        );
    }

    public function endpoint_details_meta_box($post) {
        wp_nonce_field('cac_pro_meta_box', 'cac_pro_meta_box_nonce');
        
        $endpoint = get_post_meta($post->ID, '_cac_pro_endpoint', true);
        $methods = get_post_meta($post->ID, '_cac_pro_methods', true) ?: ['GET'];
        $access = get_post_meta($post->ID, '_cac_pro_access', true) ?: 'public';
        $roles = get_post_meta($post->ID, '_cac_pro_roles', true) ?: [];
        $cache = get_post_meta($post->ID, '_cac_pro_cache', true) ?: 0;
        $group = wp_get_post_terms($post->ID, 'cac_api_group', ['fields' => 'slugs']);
        $group = !empty($group) ? $group[0] : '';
        $behavior = get_post_meta($post->ID, '_cac_pro_behavior', true) ?: $this->default_behavior;
        
        include plugin_dir_path(__FILE__) . 'templates/endpoint-details.php';
    }

    public function endpoint_code_meta_box($post) {
        $code = get_post_meta($post->ID, '_cac_pro_code', true);
        $params = get_post_meta($post->ID, '_cac_pro_params', true) ?: [];
        $response = get_post_meta($post->ID, '_cac_pro_response', true) ?: [];
        
        include plugin_dir_path(__FILE__) . 'templates/endpoint-code.php';
    }

    public function helpers_meta_box($post) {
        include plugin_dir_path(__FILE__) . 'templates/helpers-list.php';
    }

    public function save_meta_boxes($post_id) {
        if (!isset($_POST['cac_pro_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['cac_pro_meta_box_nonce'], 'cac_pro_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save endpoint details
        if (isset($_POST['cac_pro_endpoint'])) {
            update_post_meta($post_id, '_cac_pro_endpoint', sanitize_text_field($_POST['cac_pro_endpoint']));
        }
        
        if (isset($_POST['cac_pro_methods'])) {
            update_post_meta($post_id, '_cac_pro_methods', array_map('sanitize_text_field', $_POST['cac_pro_methods']));
        }
        
        if (isset($_POST['cac_pro_access'])) {
            update_post_meta($post_id, '_cac_pro_access', sanitize_text_field($_POST['cac_pro_access']));
        }
        
        if (isset($_POST['cac_pro_roles'])) {
            update_post_meta($post_id, '_cac_pro_roles', array_map('sanitize_text_field', $_POST['cac_pro_roles']));
        }
        
        if (isset($_POST['cac_pro_cache'])) {
            update_post_meta($original_post_id, '_cac_pro_cache', absint($_POST['cac_pro_cache']));
        }
        
        if (isset($_POST['cac_pro_group'])) {
            wp_set_post_terms($post_id, sanitize_text_field($_POST['cac_pro_group']), 'cac_api_group');
        }
        
        if (isset($_POST['behavior_selection'])) {
            update_post_meta($post_id, '_cac_pro_behavior', sanitize_text_field($_POST['behavior_selection']));
        }
        
        // Save code
        if (isset($_POST['cac_pro_code'])) {
            update_post_meta($post_id, '_cac_pro_code', $_POST['cac_pro_code']);
        }
        
        // Save params
        if (isset($_POST['cac_pro_params'])) {
            $params = json_decode(stripslashes($_POST['cac_pro_params']), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                update_post_meta($post_id, '_cac_pro_params', $params);
            }
        }
        
        // Save response
        if (isset($_POST['cac_pro_response'])) {
            $response = json_decode(stripslashes($_POST['cac_pro_response']), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                update_post_meta($post_id, '_cac_pro_response', $response);
            }
        }
    }

    public function register_api_endpoints() {
        $endpoints = get_posts([
            'post_type' => 'cac_api',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        foreach ($endpoints as $endpoint) {
            $endpoint_path = get_post_meta($endpoint->ID, '_cac_pro_endpoint', true);
            $methods = get_post_meta($endpoint->ID, '_cac_pro_methods', true);
            $code = get_post_meta($endpoint->ID, '_cac_pro_code', true);
            $cache = get_post_meta($endpoint->ID, '_cac_pro_cache', true);
            $behavior = get_post_meta($endpoint->ID, '_cac_pro_behavior', true) ?: $this->default_behavior;
            
            if (!$endpoint_path || !$methods) {
                continue;
            }
            
            $this->register_endpoint($endpoint_path, $methods, $code, $cache, $endpoint->ID, $behavior);
        }
    }

    private function register_endpoint($path, $methods, $code, $cache, $endpoint_id, $behavior) {
        register_rest_route('cac-pro/v1', $path, [
            'methods' => $methods,
            'callback' => function($request) use ($code, $cache, $endpoint_id, $behavior) {
                // Check cache first
                $cache_key = 'cac_pro_endpoint_' . $endpoint_id;
                if ($cache > 0) {
                    $cached = get_transient($cache_key);
                    if ($cached !== false) {
                        return $cached;
                    }
                }
                
                // Execute the code
                $response = $this->execute_endpoint_code($code, $request, $endpoint_id, $behavior);
                
                // Cache the response if needed
                if ($cache > 0) {
                    set_transient($cache_key, $response, $cache);
                }
                
                return $response;
            },
            'permission_callback' => function($request) use ($endpoint_id) {
                $access = get_post_meta($endpoint_id, '_cac_pro_access', true);
                $roles = get_post_meta($endpoint_id, '_cac_pro_roles', true);
                
                if ($access === 'public') {
                    return true;
                }
                
                if (!is_user_logged_in()) {
                    return false;
                }
                
                if (empty($roles)) {
                    return true;
                }
                
                $user = wp_get_current_user();
                return array_intersect($roles, $user->roles);
            }
        ]);
    }

    private function execute_endpoint_code($code, $request, $endpoint_id, $behavior) {
        // Prepare variables
        $params = $request->get_params();
        $headers = $request->get_headers();
        $method = $request->get_method();
        $body = $request->get_body();
        $user = wp_get_current_user();
        
        // Create a safe execution environment
        try {
            // Extract helper functions from the code
            $helper_calls = $this->extract_helper_calls($code);
            
            // Prepare helper functions
            $helper_functions = '';
            foreach ($helper_calls as $helper) {
                if (isset($this->helpers[$helper])) {
                    $helper_functions .= $this->helpers[$helper] . "\n";
                }
            }
            
            // Create the function
            $function_code = "function cac_pro_endpoint_$endpoint_id(\$params, \$headers, \$method, \$body, \$user, \$behavior) {\n" .
                            $helper_functions . "\n" .
                            $code . "\n" .
                            "}";
            
            // Execute the function
            eval128($function_code);
            $function_name = "cac_pro_endpoint_$endpoint_id";
            $result = $function_name($params, $headers, $method, $body, $user, $behavior);
            
            return $result;
        } catch (Exception $e) {
            $this->log_error('Endpoint execution error: ' . $e->getMessage());
            return [
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function extract_helper_calls($code) {
        $helpers = [];
        $pattern = '/cac_pro_helper_([a-z0-9_]+)\(/i';
        
        if (preg_match_all($pattern, $code, $matches)) {
            $helpers = array_unique($matches[1]);
        }
        
        return $helpers;
    }

    public function register_plugin_endpoints() {
        // Register helper endpoints
        register_rest_route('cac-pro/v1', '/helpers', [
            'methods' => 'GET',
            'callback' => [$this, 'get_helpers_list'],
            'permission_callback' => [$this, 'check_admin_permission']
        ]);
        
        register_rest_route('cac-pro/v1', '/helpers/(?P<name>[a-z0-9_]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_helper_code'],
            'permission_callback' => [$this, 'check_admin_permission']
        ]);
        
        // Endpoint management
        register_rest_route('cac-pro/v1', '/endpoints', [
            'methods' => 'GET',
            'callback' => [$this, 'get_endpoints_list'],
            'permission_callback' => [$this, 'check_admin_permission']
        ]);
        
        register_rest_route('cac-pro/v1', '/endpoints/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_endpoint_details'],
            'permission_callback' => [$this, 'check_admin_permission']
        ]);
    }

    public function get_helpers_list($request) {
        return array_keys($this->helpers);
    }

    public function get_helper_code($request) {
        $name = $request['name'];
        if (isset($this->helpers[$name])) {
            return [
                'name' => $name,
                'code' => $this->helpers[$name]
            ];
        }
        return new WP_Error('not_found', 'Helper not found', ['status' => 404]);
    }

    public function get_endpoints_list($request) {
        $endpoints = get_posts([
            'post_type' => 'cac_api',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        $result = [];
        foreach ($endpoints as $endpoint) {
            $result[] = [
                'id' => $endpoint->ID,
                'title' => $endpoint->post_title,
                'endpoint' => get_post_meta($endpoint->ID, '_cac_pro_endpoint', true),
                'methods' => get_post_meta($endpoint->ID, '_cac_pro_methods', true),
                'group' => wp_get_post_terms($endpoint->ID, 'cac_api_group', ['fields' => 'names'])
            ];
        }
        
        return $result;
    }

    public function get_endpoint_details($request) {
        $id = $request['id'];
        $endpoint = get_post($id);
        
        if (!$endpoint || $endpoint->post_type !== 'cac_api') {
            return new WP_Error('not_found', 'Endpoint not found', ['status' => 404]);
        }
        
        return [
            'id' => $endpoint->ID,
            'title' => $endpoint->post_title,
            'endpoint' => get_post_meta($endpoint->ID, '_cac_pro_endpoint', true),
            'methods' => get_post_meta($endpoint->ID, '_cac_pro_methods', true),
            'code' => get_post_meta($endpoint->ID, '_cac_pro_code', true),
            'params' => get_post_meta($endpoint->ID, '_cac_pro_params', true),
            'response' => get_post_meta($endpoint->ID, '_cac_pro_response', true),
            'access' => get_post_meta($endpoint->ID, '_cac_pro_access', true),
            'roles' => get_post_meta($endpoint->ID, '_cac_pro_roles', true),
            'cache' => get_post_meta($endpoint->ID, '_cac_pro_cache', true),
            'group' => wp_get_post_terms($endpoint->ID, 'cac_api_group', ['fields' => 'names']),
            'behavior' => get_post_meta($endpoint->ID, '_cac_pro_behavior', true) ?: $this->default_behavior
        ];
    }

    public function check_admin_permission() {
        return current_user_can('manage_options');
    }

    public function admin_page() {
        include plugin_dir_path(__FILE__) . 'templates/admin.php';
    }

    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        if (isset($_POST['cac_pro_settings'])) {
            check_admin_referer('cac_pro_settings');
            $this->save_settings($_POST);
        }
        
        include plugin_dir_path(__FILE__) . 'templates/settings.php';
    }
public function helper_page() {
        if (!current_user_ccan('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $helper_id = isset($_GET['helper']) ? absint($_GET['helper']) : 0;

        switch ($action) {
            case 'add':
                $this->render_helper_form();
                break;
            case 'edit':
                $this->render_helper_form($helper_id);
                break;
            case 'delete':
                $this->delete_helper($helper_id);
                wp_redirect(admin_url('admin.php?page=cac-pro-helpers'));
                exit;
            default:
                $this->list_helpers();
        }
    }

    private function render_helper_form($helper_id = 0) {
        $helper = $helper_id ? $this->get_helper($helper_id) : [
            'name' => '',
            'code' => '',
            'description' => ''
        ];

        wp_enqueue_code_editor(['type' => 'application/x-httpd-php']);
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-theme-plugin-editor');

        include plugin_dir_path(__FILE__) . 'templates/helper-form.php';
    }

    private function save_helper($data) {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $helper = [
            'name' => sanitize_text_field($data['helper_name']),
            'code' => wp_unslash($data['helper_code']),
            'description' => sanitize_textarea_field($data['helper_description'])
        ];

        if (empty($helper['name'])) {
            wp_die(__('Helper name cannot be empty.'));
        }

        if (empty($helper['code'])) {
            wp_die(__('Helper code cannot be empty.'));
        }

        // Validate PHP syntax
        if (!function_exists('token_get_all')) {
            wp_die(__('PHP tokenizer extension is required to validate helper code.'));
        }

        try {
            token_get_all('<?php ' . $helper['code']);
        } catch (ParseError $e) {
            wp_die(__('Invalid PHP code: ') . $e->getMessage());
        }

        $helper_id = isset($data['helper_id']) ? absint($data['helper_id']) : 0;

        if ($helper_id) {
            $this->update_helper($helper_id, $helper);
        } else {
            $this->add_helper($helper);
        }

        wp_redirect(admin_url('admin.php?page=cac-pro-helpers'));
        exit;
    }

    private function add_helper($helper) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'cac_helpers',
            [
                'name' => $helper['name'],
                'code' => $helper['code'],
                'description' => $helper['description'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );

        if (!$result) {
            wp_die(__('Error saving helper to database.'));
        }

        $this->helper_loaded = false;
        $this->load_helpers();
    }

    private function update_helper($helper_id, $helper) {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'cac_helpers',
            [
                'name' => $helper['name'],
                'code' => $helper['code'],
                'description' => $helper['description'],
                'updated_at' => current_time('mysql')
            ],
            ['id' => $helper_id],
            ['%s', '%s', '%s', '%s'],
            ['%d']
        );

        if (false === $result) {
            wp_die(__('Error updating helper in database.'));
        }

        $this->helper_loaded = false;
        $this->load_helpers();
    }

    private function delete_helper($helper_id) {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'cac_helpers',
            ['id' => $helper_id],
            ['%d']
        );

        if (!$result) {
            wp_die(__('Error deleting helper from database.'));
        }

        $this->helper_loaded = false;
        $this->load_helpers();
    }

    private function list_helpers() {
        global $wpdb;
        
        $helpers = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}cac_helpers ORDER BY name ASC"
        );

        include plugin_dir_path(__FILE__) . 'templates/helper-list.php';
    }

    private function get_helper($helper_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}cac_helpers WHERE id = %d",
                $helper_id
            ),
            ARRAY_A
        );
    }

    private function load_helpers() {
        if ($this->helper_loaded) {
            return;
        }

        global $wpdb;
        
        $this->helpers = [];
        $results = $wpdb->get_results("SELECT name, code FROM {$wpdb->prefix}cac_helpers");
        
        foreach ($results as $helper) {
            $this->helpers[$helper->name] = $helper->code;
        }

        $this->helper_loaded = true;
    }

    private function load_settings() {
        $this->settings = get_option('cac_pro_settings', [
            'enable_cache' => true,
            'cache_duration' => 3600,
            'enable_logging' => true,
            'log_level' => 'error'
        ]);
        $this->default_behavior = get_option('cac_pro_default_behavior', 'new');
    }

    private function save_settings($data) {
        $settings = [
            'enable_cache' => isset($data['enable_cache']),
            'cache_duration' => absint($data['cache_duration']),
            'enable_logging' => isset($data['enable_logging']),
            'log_level' => in_array($data['log_level'], ['debug', 'info', 'warning', 'error']) 
                ? $data['log_level'] 
                : 'error'
        ];

        update_option('cac_pro_settings', $settings);
        $this->settings = $settings;

        if (isset($data['cac_pro_default_behavior'])) {
            update_option('cac_pro_default_behavior', sanitize_text_field($data['cac_pro_default_behavior']));
            $this->default_behavior = sanitize_text_field($data['cac_pro_default_behavior']);
        }
    }

    private function log_error($message) {
        if (!$this->settings['enable_logging']) {
            return;
        }

        if (!file_exists($this->log_path)) {
            wp_mkdir_p($this->log_path);
        }

        $log_file = $this->log_path . 'error.log';
        $message = '[' . current_time('mysql') . '] ' . $message . "\n";
        file_put_contents($log_file, $message, FILE_APPEND);
    }

    private function register_default_helpers() {
        $this->helpers = array_merge($this->helpers, [
            'get_post_data' => 'function get_post_data($post_id) {
                $post = get_post($post_id);
                if (!$post) {
                    return false;
                }
                return [
                    "id" => $post->ID,
                    "title" => $post->post_title,
                    "content" => $post->post_content,
                    "excerpt" => $post->post_excerpt,
                    "status" => $post->post_status,
                    "date" => $post->post_date
                ];
            }',
            'get_user_data' => 'function get_user_data($user_id) {
                $user = get_user_by("id", $user_id);
                if (!$user) {
                    return false;
                }
                return [
                    "id" => $user->ID,
                    "username" => $user->user_login,
                    "email" => $user->user_email,
                    "roles" => $user->roles
                ];
            }',
            'validate_email' => 'function validate_email($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            }'
        ]);
    }

    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'cac_helpers';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            code text NOT NULL,
            description text,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY name (name)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('cac_pro_settings', [
            'enable_cache' => true,
            'cache_duration' => 3600,
            'enable_logging' => true,
            'log_level' => 'error'
        ]);
        add_option('cac_pro_default_behavior', 'new');
    }

    public function deactivate() {
        // Clean up on deactivation
    }

    public function uninstall() {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cac_helpers");
        delete_option('cac_pro_settings');
        delete_option('cac_pro_default_behavior');
    }
}

// Initialize the plugin
function cac_pro_init() {
    $GLOBALS['cac_pro'] = CAC_Plugin_Pro::get_instance();
}
add_action('plugins_loaded', 'cac_pro_init');

// Register activation/deactivation hooks
register_activation_hook(__FILE__, ['CAC_Plugin_Pro', 'activate']);
register_deactivation_hook(__FILE__, ['CAC_Plugin_Proxy', 'deactivate']);
register_uninstall_hook(__FILE__, ['CAC_Plugin_Pro', 'uninstall']);
