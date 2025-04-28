<?php
/**
 * Plugin Name: Custom API Creator
 * Plugin URI: https://wordpress.org/plugins/custom-api-creator/
 * Description: Create custom APIs with flexible output and user roles.
 * Version: 1.0.4
 * Author: Mehdi Rezaei
 * Author URI: https://mehd.ir
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cac-plugin-creator
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use SoftCreatR\JSONPath\JSONPath;

class CAC_Plugin_Class {
	public function __construct() {
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'rest_api_init', array( $this, 'register_cac_plugins' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_cac_plugin_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_cac_plugin_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// custom column
		add_filter( 'manage_cac_plugin_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_cac_plugin_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
	}

	public function register_custom_post_type() {
		$labels = array(
			'name' => _x( 'Custom APIs', 'post type general name', 'cac-plugin-creator' ),
			'singular_name' => _x( 'Custom API', 'post type singular name', 'cac-plugin-creator' ),
			'menu_name' => _x( 'Custom APIs', 'admin menu', 'cac-plugin-creator' ),
			'name_admin_bar' => _x( 'Custom API', 'add new on admin bar', 'cac-plugin-creator' ),
			'add_new' => _x( 'Add New', 'custom api', 'cac-plugin-creator' ),
			'add_new_item' => __( 'Add New API', 'cac-plugin-creator' ),
			'new_item' => __( 'New API', 'cac-plugin-creator' ),
			'edit_item' => __( 'Edit API', 'cac-plugin-creator' ),
			'view_item' => __( 'View API', 'cac-plugin-creator' ),
			'all_items' => __( 'All APIs', 'cac-plugin-creator' ),
			'search_items' => __( 'Search API', 'cac-plugin-creator' ),
			'parent_item_colon' => __( 'Parent APIs:', 'cac-plugin-creator' ),
			'not_found' => __( 'No apis found.', 'cac-plugin-creator' ),
			'not_found_in_trash' => __( 'No apis found in Trash.', 'cac-plugin-creator' )
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'cac-plugin' ),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title' )
		);

		register_post_type( 'cac_plugin', $args );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'cac-plugin-creator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Custom API', 'cac-plugin-creator' ),
			__( 'Custom API', 'cac-plugin-creator' ),
			'manage_options',
			'edit.php?post_type=cac_plugin',
			null,
			'dashicons-rest-api',
			30
		);
	}

	public function enqueue_admin_scripts( $hook ) {
		if ( 'post.php' != $hook && 'post-new.php' != $hook ) {
			return;
		}

		global $post;
		if ( 'cac_plugin' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script( 'cac-plugin-admin', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_script( 'code-editor' );
		wp_enqueue_style( 'code-editor' );
	}

	public function add_cac_plugin_meta_boxes() {
		add_meta_box(
			'cac_plugin_details',
			__( 'API Details', 'cac-plugin-creator' ),
			array( $this, 'render_api_details_meta_box' ),
			'cac_plugin',
			'normal',
			'high'
		);
	}

	public function render_api_details_meta_box( $post ) {
		wp_nonce_field( 'cac_plugin_meta_box', 'cac_plugin_meta_box_nonce' );

		$endpoint = get_post_meta( $post->ID, '_cac_plugin_endpoint', true );
		$http_methods = get_post_meta( $post->ID, '_cac_plugin_http_methods', true ) ?: array( 'GET' ); // Default to GET
		$action_function = get_post_meta( $post->ID, '_cac_plugin_action_function', true );
		$request_config = get_post_meta( $post->ID, '_cac_plugin_request_config', true ) ?: array();
		$response_config = get_post_meta( $post->ID, '_cac_plugin_response_config', true ) ?: array();
		$option_data = get_post_meta( $post->ID, '_cac_plugin_option_data', true ); // New option data

		?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="cac_plugin_endpoint"><?php esc_html_e( 'API Endpoint', 'cac-plugin-creator' ); ?></label></th>
				<td>
					<input type="text" id="cac_plugin_endpoint" name="cac_plugin_endpoint"
						value="<?php echo esc_attr( $endpoint ); ?>" class="regular-text" required>
					<p class="description"><?php esc_html_e( 'Example: my-cac-plugin/[parameter]', 'cac-plugin-creator' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'HTTP Methods', 'cac-plugin-creator' ); ?></th>
				<td>
					<fieldset>
						<?php
						$methods = array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' );
						foreach ( $methods as $method ) :
							$checked = in_array( $method, $http_methods );
							?>
							<label>
								<input type="checkbox" name="cac_plugin_http_methods[]" value="<?php echo esc_attr( $method ); ?>" <?php checked( $checked ); ?>>
								<?php echo esc_html( $method ); ?>
							</label><br>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cac_plugin_action_function"><?php esc_html_e( 'Handler Function', 'cac-plugin-creator' ); ?></label></th>
				<td>
					<textarea id="cac_plugin_action_function" name="cac_plugin_action_function" class="code-editor" rows="10" cols="50" style="width: 100%;"><?php echo esc_textarea( $action_function ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Write the handler function in PHP. Syntax highlighting is supported.', 'cac-plugin-creator' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Request Configuration', 'cac-plugin-creator' ); ?></th>
				<td>
					<textarea id="cac_plugin_request_config" name="cac_plugin_request_config" rows="5" cols="50" style="width: 100%;"><?php echo esc_textarea( json_encode( $request_config, JSON_PRETTY_PRINT ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Define the request configuration (headers, body, status) in JSON format.', 'cac-plugin-creator' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Response Configuration', 'cac-plugin-creator' ); ?></th>
				<td>
					<textarea id="cac_plugin_response_config" name="cac_plugin_response_config" rows="5" cols="50" style="width: 100%;"><?php echo esc_textarea( json_encode( $response_config, JSON_PRETTY_PRINT ) ); ?></textarea>
<p class="description"><?php esc_html_e( 'Define the response configuration (headers, body, status) in JSON format.', 'cac-plugin-creator' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cac_plugin_option_data"><?php esc_html_e( 'Option Data', 'cac-plugin-creator' ); ?></label></th>
				<td>
					<textarea id="cac_plugin_option_data" name="cac_plugin_option_data" rows="5" class="regular-text"><?php echo esc_textarea( $option_data ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Store additional data as an option for this API.', 'cac-plugin-creator' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save_cac_plugin_meta( $post_id ) {
		if ( ! isset( $_POST['cac_plugin_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cac_plugin_meta_box_nonce'] ) ), 'cac_plugin_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_cac_plugin_endpoint'        => 'cac_plugin_endpoint',
			'_cac_plugin_http_methods'    => 'cac_plugin_http_methods',
			'_cac_plugin_action_function' => 'cac_plugin_action_function',
			'_cac_plugin_request_config'  => 'cac_plugin_request_config',
			'_cac_plugin_response_config' => 'cac_plugin_response_config',
			'_cac_plugin_option_data'     => 'cac_plugin_option_data', // New field
		);

		foreach ( $fields as $meta_key => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				$value = is_array( $_POST[ $post_key ] ) ? array_map( 'sanitize_text_field', $_POST[ $post_key ] ) : sanitize_text_field( $_POST[ $post_key ] );
				if ( in_array( $meta_key, array( '_cac_plugin_request_config', '_cac_plugin_response_config' ), true ) ) {
					$value = json_decode( $value, true );
					if (json_last_error() !== JSON_ERROR_NONE) {
						$this->log_message('Invalid JSON data provided for ' . $meta_key, 'error');
						continue;
					}
				}
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	public function register_cac_plugins() {
		$cac_plugins = get_posts( array(
			'post_type' => 'cac_plugin',
			'posts_per_page' => -1,
		) );

		foreach ( $cac_plugins as $api ) {
			$endpoint = get_post_meta( $api->ID, '_cac_plugin_endpoint', true );
			$http_methods = get_post_meta( $api->ID, '_cac_plugin_http_methods', true );
			$action_function = get_post_meta( $api->ID, '_cac_plugin_action_function', true );
			$request_config = get_post_meta( $api->ID, '_cac_plugin_request_config', true );
			$response_config = get_post_meta( $api->ID, '_cac_plugin_response_config', true );

			if ( ! $http_methods || ! is_array( $http_methods ) ) {
				$http_methods = array( 'GET' );
			}

			register_rest_route( 'cac-plugin/v1', '/' . ltrim( $endpoint, '/' ), array(
				'methods' => $http_methods,
				'callback' => function ( $request ) use ( $action_function, $request_config, $response_config ) {
					// Log the incoming request
					$this->log_message( 'Incoming request: ' . print_r( $request->get_params(), true ), 'info' );

					// Validate and apply request configuration
					if ( $request_config ) {
						foreach ( $request_config['headers'] as $header => $value ) {
							$request->set_header( $header, $value );
						}
					}

					// Call the handler function
					if ( $action_function && is_callable( $action_function ) ) {
						$response = call_user_func( $action_function, $request );
					} else {
						$response = array( 'message' => 'No valid handler function defined.' );
					}

					// Log the response
					$this->log_message( 'Response: ' . print_r( $response, true ), 'info' );

					// Apply response configuration
					if ( $response_config ) {
						return new WP_REST_Response( $response_config['body'] ?? $response, $response_config['status'] ?? 200, $response_config['headers'] ?? array() );
					}

					return $response;
				},
				'permission_callback' => function () use ( $api ) {
					$roles = get_post_meta( $api->ID, '_cac_plugin_roles', true );
					return $this->check_api_permissions( $roles );
				},
			) );
		}
	}

	public function handle_api_request( $request ) {
		$params = $request->get_params();
		$endpoint = $request->get_route();

		$api_post = $this->get_api_by_endpoint( substr( $endpoint, strlen( '/cac-plugin/v1/' ) ) );
		if ( ! $api_post ) {
			return new WP_Error( 'invalid_api', 'Invalid API endpoint', array( 'status' => 404 ) );
		}

		$access_type = get_post_meta( $api_post->ID, '_cac_plugin_access_type', true );
		$roles = get_post_meta( $api_post->ID, '_cac_plugin_roles', true );

		if ( $access_type === 'private' && ! $this->check_api_permissions( $roles ) ) {
			return new WP_Error( 'unauthorized', 'You do not have permission to access this API', array( 'status' => 403 ) );
		}

		$sections = get_post_meta( $api_post->ID, '_cac_plugin_sections', true );

		$response = array();
		foreach ( $sections as $section ) {
			$section_name = ! empty( $section['name'] ) ? sanitize_title( $section['name'] ) : 'section_' . $section['post_type'];
			$query_args = $this->build_query_args( $section['post_type'], $section['taxonomies'], $params );
			$posts = $this->get_posts( $query_args );
			$response[ $section_name ] = $this->format_response( $posts, $section['fields'] );
		}

		return $response;
	}

	private function get_api_by_endpoint( $endpoint ) {
		$endpoint = str_replace( 'cac-plugin/v1/', '', $endpoint );

		$cac_plugins = get_posts( array(
			'post_type' => 'cac_plugin',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_cac_plugin_endpoint',
					'value' => $endpoint,
					'compare' => '='
				)
			)
		) );

		return ! empty( $cac_plugins ) ? $cac_plugins[0] : null;
	}

	private function check_api_permissions( $allowed_roles ) {
		if ( empty( $allowed_roles ) ) {
			return true;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();
		$user_roles = (array) $user->roles;

		return array_intersect( $allowed_roles, $user_roles );
	}

	private function build_query_args( $post_type, $taxonomies, $params ) {
		$query_args = array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
		);

		if ( ! empty( $taxonomies ) ) {
			$tax_query = array();
			foreach ( $taxonomies as $taxonomy ) {
				if ( isset( $params[ $taxonomy ] ) ) {
					$tax_query[] = array(
						'taxonomy' => $taxonomy,
						'field' => 'slug',
						'terms' => explode( ',', $params[ $taxonomy ] ),
					);
				}
			}
			if ( ! empty( $tax_query ) ) {
				$query_args['tax_query'] = $tax_query;
			}
		}

		if ( isset( $params['search'] ) ) {
			$query_args['s'] = $params['search'];
		}

		return $query_args;
	}

	private function get_posts( $query_args ) {
		$query = new WP_Query( $query_args );
		return $query->posts;
	}

	private function format_response( $posts, $fields ) {
		$response = array();
		foreach ( $posts as $post ) {
			$item = array();
			foreach ( $fields as $field ) {
				switch ( $field ) {
					case 'title':
						$item['title'] = get_the_title( $post->ID );
						break;
					case 'content':
						$item['content'] = get_the_content( null, false, $post->ID );
						break;
					case 'excerpt':
						$item['excerpt'] = get_the_excerpt( $post->ID );
						break;
					case 'categories':
						$item['categories'] = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
						break;
					case 'tags':
						$item['tags'] = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
						break;
					case 'custom_fields':
						$item['custom_fields'] = get_post_custom( $post->ID );
						break;
				}
			}
			$response[] = $item;
		}
		return $response;
	}

	public function add_custom_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'title' ) {
				$new_columns['endpoint'] = __( 'Endpoint', 'cac-plugin-creator' );
				$new_columns['permission'] = __( 'Permission', 'cac-plugin-creator' );
			}
		}
		return $new_columns;
	}

	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'endpoint':
				$endpoint = get_post_meta( $post_id, '_cac_plugin_endpoint', true );
				if ( $endpoint ) {
					$link = esc_url( home_url( '/wp-json/cac-plugin/v1/' . ltrim( $endpoint, '/' ) ) );
					echo '<a href="' . $link . '" target="_blank">' . $link . "</a>";
				} else {
					echo esc_html( '—' );
				}
				break;

			case 'permission':
				$access_type = get_post_meta( $post_id, '_cac_plugin_access_type', true );
				if ( $access_type === 'public' ) {
					esc_html_e( 'Public', 'cac-plugin-creator' );
				} else {
					$roles = get_post_meta( $post_id, '_cac_plugin_roles', true );
					if ( ! empty( $roles ) ) {
						$role_names = array_map( function ($role) {
							return translate_user_role( $role );
						}, $roles );
						echo esc_html( implode( ', ', $role_names ) );
					} else {
						esc_html_e( 'No roles specified', 'cac-plugin-creator' );
					}
				}
				break;
		}
	}

	// Add logging functionality
	public function log_message( $message, $level = 'info' ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$log_message = sprintf( "[%s] [%s]: %s\n", date( 'Y-m-d H:i:s' ), strtoupper( $level ), $message );
			error_log( $log_message );
		}
	}

	// Save data to a transient
	public function set_transient( $key, $data, $expiration = 3600 ) {
		set_transient( $key, $data, $expiration );
	}

	// Retrieve data from a transient
	public function get_transient( $key ) {
		return get_transient( $key );
	}

	// Delete a transient
	public function delete_transient( $key ) {
		delete_transient( $key );
	}

	// Include JSONPath
	use SoftCreatR\JSONPath\JSONPath;

	public function extract_data_with_jsonpath( $json_data, $jsonpath_query ) {
		// Parse JSON data
		$data = json_decode( $json_data, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->log_message( 'Invalid JSON data provided.', 'error' );
			return null;
		}

		// Apply JSONPath query
		$jsonpath = new JSONPath( $data );
		return $jsonpath->find( $jsonpath_query )->data();
	}

	// Transform data
	public function transform_data( $data, $transformation_function ) {
		// Apply user-defined transformation function
		return array_map( $transformation_function, $data );
	}

	// Load data (e.g., save to database)
	public function load_data( $data, $post_type = 'post' ) {
		foreach ( $data as $item ) {
			// Insert or update a WordPress post
			wp_insert_post( array(
				'post_type'    => $post_type,
				'post_content' => json_encode( $item ),
				'post_status'  => 'publish',
			) );
		}
	}
}

new CAC_Plugin_Class();
