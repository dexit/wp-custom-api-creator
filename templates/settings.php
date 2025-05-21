<?php
/**
 * Settings Page Template
 *
 * @package Custom_API_Creator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Custom API Creator Settings', 'cac-pro' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'cac_pro_settings_group' ); ?>
		<?php do_settings_sections( 'cac_pro_settings_group' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Default Behavior', 'cac-pro' ); ?></th>
				<td>
					<select name="cac_pro_default_behavior" id="cac_pro_default_behavior">
						<option value="new" <?php selected( get_option( 'cac_pro_default_behavior' ), 'new' ); ?>><?php esc_html_e( 'New Functionality', 'cac-pro' ); ?></option>
						<option value="old" <?php selected( get_option( 'cac_pro_default_behavior' ), 'old' ); ?>><?php esc_html_e( 'Old Behavior', 'cac-pro' ); ?></option>
					</select>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
