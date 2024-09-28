<?php
/**
 * Plugin Name: Custom API Creator
 * Plugin URI: https://github.com/mehdiraized/wp-custom-api-creator
 * Description: Create custom APIs with flexible output and user roles.
 * Version: 1.0.1
 * Author: Mehdi Rezaei
 * Author URI: https://mehd.ir
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-api-creator
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Custom_API_Creator {
	public function __construct() {
		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'rest_api_init', array( $this, 'register_custom_apis' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_custom_api_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_custom_api_meta' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// custom column
		add_filter( 'manage_custom_api_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_custom_api_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
	}

	public function register_custom_post_type() {
		$labels = array(
			'name' => _x( 'Custom APIs', 'post type general name', 'custom-api-creator' ),
			'singular_name' => _x( 'Custom API', 'post type singular name', 'custom-api-creator' ),
			'menu_name' => _x( 'Custom APIs', 'admin menu', 'custom-api-creator' ),
			'name_admin_bar' => _x( 'Custom API', 'add new on admin bar', 'custom-api-creator' ),
			'add_new' => _x( 'Add New', 'custom api', 'custom-api-creator' ),
			'add_new_item' => __( 'Add New API', 'custom-api-creator' ),
			'new_item' => __( 'New API', 'custom-api-creator' ),
			'edit_item' => __( 'Edit API', 'custom-api-creator' ),
			'view_item' => __( 'View API', 'custom-api-creator' ),
			'all_items' => __( 'All APIs', 'custom-api-creator' ),
			'search_items' => __( 'Search API', 'custom-api-creator' ),
			'parent_item_colon' => __( 'Parent APIs:', 'custom-api-creator' ),
			'not_found' => __( 'No apis found.', 'custom-api-creator' ),
			'not_found_in_trash' => __( 'No apis found in Trash.', 'custom-api-creator' )
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'custom-api' ),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title' )
		);

		register_post_type( 'custom_api', $args );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'custom-api-creator', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Custom API', 'custom-api-creator' ),
			__( 'Custom API', 'custom-api-creator' ),
			'manage_options',
			'edit.php?post_type=custom_api',
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
		if ( 'custom_api' !== $post->post_type ) {
			return;
		}

		wp_enqueue_script( 'custom-api-admin', plugin_dir_url( __FILE__ ) . 'assets/js/script.js', array( 'jquery' ), '1.0', true );
	}

	public function add_custom_api_meta_boxes() {
		add_meta_box(
			'custom_api_details',
			__( 'API Details', 'custom-api-creator' ),
			array( $this, 'render_api_details_meta_box' ),
			'custom_api',
			'normal',
			'high'
		);
	}

	public function render_api_details_meta_box( $post ) {
		wp_nonce_field( 'custom_api_meta_box', 'custom_api_meta_box_nonce' );

		$endpoint = get_post_meta( $post->ID, '_custom_api_endpoint', true );
		$sections = get_post_meta( $post->ID, '_custom_api_sections', true );
		$access_type = get_post_meta( $post->ID, '_custom_api_access_type', true ) ?: 'public';
		$roles = get_post_meta( $post->ID, '_custom_api_roles', true ) ?: array();

		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$all_roles = wp_roles()->get_names();
		$all_taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		?>
		<table class="form-table">
			<tr>
				<th scope="row"><label
						for="custom_api_endpoint"><?php esc_html_e( 'API Endpoint', 'custom-api-creator' ); ?></label></th>
				<td>
					<input type="text" id="custom_api_endpoint" name="custom_api_endpoint"
						value="<?php echo esc_attr( $endpoint ); ?>" class="regular-text" required>
					<p class="description"><?php esc_html_e( 'Example: my-custom-api/[parameter]', 'custom-api-creator' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'API Sections', 'custom-api-creator' ); ?></th>
				<td>
					<div id="api_sections">
						<?php
						if ( ! empty( $sections ) && is_array( $sections ) ) {
							foreach ( $sections as $index => $section ) {
								$this->render_section_fields( $post_types, $all_taxonomies, $index, $section );
							}
						} else {
							$this->render_section_fields( $post_types, $all_taxonomies, 0 );
						}
						?>
					</div>
					<button type="button" id="add_section"
						class="button"><?php esc_html_e( 'Add Section', 'custom-api-creator' ); ?></button>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Access Type', 'custom-api-creator' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e( 'Access Type', 'custom-api-creator' ); ?></legend>
						<label>
							<input type="radio" name="custom_api_access_type" value="public" <?php checked( $access_type, 'public' ); ?>>
							<?php esc_html_e( 'Public', 'custom-api-creator' ); ?>
						</label>
						<br>
						<label>
							<input type="radio" name="custom_api_access_type" value="private" <?php checked( $access_type, 'private' ); ?>>
							<?php esc_html_e( 'Private', 'custom-api-creator' ); ?>
						</label>
					</fieldset>
				</td>
			</tr>
			<tr id="custom_api_roles_row" style="<?php echo $access_type === 'private' ? '' : 'display: none;'; ?>">
				<th scope="row"><?php esc_html_e( 'User Roles', 'custom-api-creator' ); ?></th>
				<td>
					<?php foreach ( $all_roles as $role => $name ) :
						$checked = in_array( $role, $roles );
						?>
						<label><input type="checkbox" name="custom_api_roles[]" value="<?php echo esc_attr( $role ); ?>" <?php checked( $checked ); ?>> <?php echo esc_html( $name ); ?></label><br>
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	private function render_section_fields( $post_types, $all_taxonomies, $index, $section = null ) {
		$section = $section ?: array( 'name' => '1', 'post_type' => '', 'fields' => array(), 'taxonomies' => array() );
		?>
		<div class="api-section" data-index="<?php echo esc_attr( $index ); ?>">
			<h4><?php printf( esc_html__( 'Section %d', 'custom-api-creator' ), $index + 1 ); ?></h4>
			<p>
				<label>
					<?php esc_html_e( 'Property Name:', 'custom-api-creator' ); ?>
					<input type="text" name="custom_api_sections[<?php echo esc_attr( $index ); ?>][name]"
						value="<?php echo esc_attr( $section['name'] ); ?>" class="regular-text">
				</label>
			</p>
			<p>
				<label>
					<?php esc_html_e( 'Post Type:', 'custom-api-creator' ); ?>
					<select name="custom_api_sections[<?php echo esc_attr( $index ); ?>][post_type]" class="section-post-type">
						<?php foreach ( $post_types as $pt ) : ?>
							<option value="<?php echo esc_attr( $pt->name ); ?>" <?php selected( $section['post_type'], $pt->name ); ?>>
								<?php echo esc_html( $pt->label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</label>
			</p>
			<p>
				<label><?php esc_html_e( 'Fields:', 'custom-api-creator' ); ?></label><br>
				<?php
				$available_fields = array( 'title', 'content', 'excerpt', 'categories', 'tags' );
				foreach ( $available_fields as $field ) :
					$checked = in_array( $field, $section['fields'] );
					?>
					<label><input type="checkbox" name="custom_api_sections[<?php echo esc_attr( $index ); ?>][fields][]"
							value="<?php echo esc_attr( $field ); ?>" <?php checked( $checked ); ?>>
						<?php echo esc_html( ucfirst( $field ) ); ?></label><br>
				<?php endforeach; ?>
			</p>
			<p>
				<label><?php esc_html_e( 'Taxonomies:', 'custom-api-creator' ); ?></label><br>
				<?php foreach ( $all_taxonomies as $tax ) :
					$checked = in_array( $tax->name, $section['taxonomies'] );
					?>
					<label><input type="checkbox" name="custom_api_sections[<?php echo esc_attr( $index ); ?>][taxonomies][]"
							value="<?php echo esc_attr( $tax->name ); ?>" <?php checked( $checked ); ?>>
						<?php echo esc_html( $tax->label ); ?></label><br>
				<?php endforeach; ?>
			</p>
			<?php if ( $index > 0 ) : ?>
				<button type="button"
					class="button remove-section"><?php esc_html_e( 'Remove Section', 'custom-api-creator' ); ?></button>
			<?php endif; ?>
		</div>
		<?php
	}

	public function save_custom_api_meta( $post_id ) {
		if ( ! isset( $_POST['custom_api_meta_box_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['custom_api_meta_box_nonce'] ) ), 'custom_api_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'_custom_api_endpoint' => 'custom_api_endpoint',
			'_custom_api_sections' => 'custom_api_sections',
			'_custom_api_access_type' => 'custom_api_access_type',
			'_custom_api_roles' => 'custom_api_roles'
		);

		foreach ( $fields as $meta_key => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				$value = $post_key === 'custom_api_sections' ? $this->sanitize_sections( sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) ) : $this->sanitize_array_or_string( sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) );
				update_post_meta( $post_id, $meta_key, $value );
			} else {
				delete_post_meta( $post_id, $meta_key );
			}
		}
	}

	private function sanitize_sections( $sections ) {
		$sanitized = array();
		foreach ( $sections as $index => $section ) {
			$sanitized[ $index ] = array(
				'name' => sanitize_text_field( $section['name'] ),
				'post_type' => sanitize_text_field( $section['post_type'] ),
				'fields' => isset( $section['fields'] ) ? array_map( 'sanitize_text_field', $section['fields'] ) : array(),
				'taxonomies' => isset( $section['taxonomies'] ) ? array_map( 'sanitize_text_field', $section['taxonomies'] ) : array()
			);
		}
		return $sanitized;
	}

	private function sanitize_array_or_string( $data ) {
		if ( is_array( $data ) ) {
			return array_map( 'sanitize_text_field', $data );
		}
		return sanitize_text_field( $data );
	}

	public function register_custom_apis() {
		$custom_apis = get_posts( array(
			'post_type' => 'custom_api',
			'posts_per_page' => -1,
		) );

		foreach ( $custom_apis as $api ) {
			$endpoint = get_post_meta( $api->ID, '_custom_api_endpoint', true );
			$roles = get_post_meta( $api->ID, '_custom_api_roles', true );

			register_rest_route( 'custom-api/v1', '/' . ltrim( $endpoint, '/' ), array(
				'methods' => 'GET',
				'callback' => array( $this, 'handle_api_request' ),
				'permission_callback' => function () use ($roles) {
					return $this->check_api_permissions( $roles );
				},
			) );
		}
	}

	public function handle_api_request( $request ) {
		$params = $request->get_params();
		$endpoint = $request->get_route();

		$api_post = $this->get_api_by_endpoint( substr( $endpoint, strlen( '/custom-api/v1/' ) ) );
		if ( ! $api_post ) {
			return new WP_Error( 'invalid_api', 'Invalid API endpoint', array( 'status' => 404 ) );
		}

		$access_type = get_post_meta( $api_post->ID, '_custom_api_access_type', true );
		$roles = get_post_meta( $api_post->ID, '_custom_api_roles', true );

		if ( $access_type === 'private' && ! $this->check_api_permissions( $roles ) ) {
			return new WP_Error( 'unauthorized', 'You do not have permission to access this API', array( 'status' => 403 ) );
		}

		$sections = get_post_meta( $api_post->ID, '_custom_api_sections', true );

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
		$endpoint = str_replace( 'custom-api/v1/', '', $endpoint );

		$custom_apis = get_posts( array(
			'post_type' => 'custom_api',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => '_custom_api_endpoint',
					'value' => $endpoint,
					'compare' => '='
				)
			)
		) );

		return ! empty( $custom_apis ) ? $custom_apis[0] : null;
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
				$new_columns['endpoint'] = __( 'Endpoint', 'custom-api-creator' );
				$new_columns['permission'] = __( 'Permission', 'custom-api-creator' );
			}
		}
		return $new_columns;
	}

	public function custom_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'endpoint':
				$endpoint = get_post_meta( $post_id, '_custom_api_endpoint', true );
				if ( $endpoint ) {
					echo esc_url( home_url( '/wp-json/custom-api/v1/' . ltrim( $endpoint, '/' ) ) );
				} else {
					echo esc_html( '—' );
				}
				break;

			case 'permission':
				$access_type = get_post_meta( $post_id, '_custom_api_access_type', true );
				if ( $access_type === 'public' ) {
					esc_html_e( 'Public', 'custom-api-creator' );
				} else {
					$roles = get_post_meta( $post_id, '_custom_api_roles', true );
					if ( ! empty( $roles ) ) {
						$role_names = array_map( function ($role) {
							return translate_user_role( $role );
						}, $roles );
						echo esc_html( implode( ', ', $role_names ) );
					} else {
						esc_html_e( 'No roles specified', 'custom-api-creator' );
					}
				}
				break;
		}
	}
}

new Custom_API_Creator();