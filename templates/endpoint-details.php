<?php
/**
 * Endpoint Details Template
 *
 * @package Custom_API_Creator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="endpoint-details">
	<h2><?php esc_html_e( 'Endpoint Details', 'cac-pro' ); ?></h2>

	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="cac_pro_endpoint"><?php esc_html_e( 'Endpoint', 'cac-pro' ); ?></label>
			</th>
			<td>
				<input type="text" name="cac_pro_endpoint" id="cac_pro_endpoint" value="<?php echo esc_attr( $endpoint ); ?>" class="regular-text" />
				<p class="description"><?php esc_html_e( 'Example: my-custom-api/[parameter]', 'cac-pro' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="cac_pro_methods"><?php esc_html_e( 'Methods', 'cac-pro' ); ?></label>
			</th>
			<td>
				<select name="cac_pro_methods[]" id="cac_pro_methods" multiple>
					<option value="GET" <?php selected( in_array( 'GET', $methods, true ) ); ?>><?php esc_html_e( 'GET', 'cac-pro' ); ?></option>
					<option value="POST" <?php selected( in_array( 'POST', $methods, true ) ); ?>><?php esc_html_e( 'POST', 'cac-pro' ); ?></option>
					<option value="PUT" <?php selected( in_array( 'PUT', $methods, true ) ); ?>><?php esc_html_e( 'PUT', 'cac-pro' ); ?></option>
					<option value="DELETE" <?php selected( in_array( 'DELETE', $methods, true ) ); ?>><?php esc_html_e( 'DELETE', 'cac-pro' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="cac_pro_access"><?php esc_html_e( 'Access Type', 'cac-pro' ); ?></label>
			</th>
			<td>
				<fieldset>
					<label>
						<input type="radio" name="cac_pro_access" value="public" <?php checked( 'public', $access ); ?> />
						<?php esc_html_e( 'Public', 'cac-pro' ); ?>
					</label>
					<br />
					<label>
						<input type="radio" name="cac_pro_access" value="private" <?php checked( 'private', $access ); ?> />
						<?php esc_html_e( 'Private', 'cac-pro' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		<tr id="custom_api_roles_row" <?php if ( 'public' === $access ) : ?>style="display: none;"<?php endif; ?>>
			<th scope="row">
				<label for="cac_pro_roles"><?php esc_html_e( 'User Roles', 'cac-pro' ); ?></label>
			</th>
			<td>
				<select name="cac_pro_roles[]" id="cac_pro_roles" multiple>
					<?php
					global $wp_roles;
					foreach ( $wp_roles->roles as $role_key => $role ) :
						?>
						<option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( in_array( $role_key, $roles, true ) ); ?>>
							<?php echo esc_html( $role['name'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="cac_pro_cache"><?php esc_html_e( 'Cache Duration (seconds)', 'cac-pro' ); ?></label>
			</th>
			<td>
				<input type="number" name="cac_pro_cache" id="cac_pro_cache" value="<?php echo esc_attr( $cache ); ?>" class="small-text" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="cac_pro_group"><?php esc_html_e( 'API Group', 'cac-pro' ); ?></label>
			</th>
			<td>
				<?php
				wp_dropdown_categories(
					array(
						'taxonomy'         => 'cac_api_group',
						'name'             => 'cac_pro_group',
						'selected'         => $group,
						'show_option_none' => __( 'None', 'cac-pro' ),
						'class'            => 'regular-text',
					)
				);
				?>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="behavior_selection"><?php esc_html_e( 'Behavior Selection', 'cac-pro' ); ?></label>
			</th>
			<td>
				<select name="behavior_selection" id="behavior_selection">
					<option value="new"><?php esc_html_e( 'New Functionality', 'cac-pro' ); ?></option>
					<option value="old"><?php esc_html_e( 'Old Behavior', 'cac-pro' ); ?></option>
				</select>
			</td>
		</tr>
	</table>
</div>
