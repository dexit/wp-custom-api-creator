<table class="form-table">
    <tr>
        <th scope="row"><?php _e('Endpoint Path', 'cac-pro'); ?></th>
        <td>
            <input type="text" name="cac_pro_endpoint" value="<?php echo esc_attr($endpoint); ?>" class="regular-text" required>
            <p class="description"><?php _e('Relative to /wp-json/cac-pro/v1/. E.g., /my-endpoint', 'cac-pro'); ?></p>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Methods', 'cac-pro'); ?></th>
        <td>
            <?php foreach (['GET', 'POST', 'PUT', 'DELETE', 'PATCH'] as $method): ?>
                <label>
                    <input type="checkbox" name="cac_pro_methods[]" value="<?php echo $method; ?>" <?php checked(in_array($method, $methods)); ?>>
                    <?php echo $method; ?>
                </label><br>
            <?php endforeach; ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Access', 'cac-pro'); ?></th>
        <td>
            <select name="cac_pro_access" id="cac_pro_access">
                <option value="public" <?php selected($access, 'public'); ?>><?php _e('Public', 'cac-pro'); ?></option>
                <option value="private" <?php selected($access, 'private'); ?>><?php _e('Private (Restricted)', 'cac-pro'); ?></option>
            </select>
        </td>
    </tr>
    <tr id="cac_pro_roles_row" style="<?php echo $access === 'private' ? '' : 'display:none;'; ?>">
        <th scope="row"><?php _e('Allowed Roles', 'cac-pro'); ?></th>
        <td>
            <?php
            $wp_roles = wp_roles();
            foreach ($wp_roles->role_names as $role_key => $role_name): ?>
                <label>
                    <input type="checkbox" name="cac_pro_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $roles)); ?>>
                    <?php echo esc_html($role_name); ?>
                </label><br>
            <?php endforeach; ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Cache (seconds)', 'cac-pro'); ?></th>
        <td>
            <input type="number" name="cac_pro_cache" value="<?php echo esc_attr($cache); ?>" class="small-text">
            <p class="description"><?php _e('0 to disable cache.', 'cac-pro'); ?></p>
        </td>
    </tr>
</table>
<script>
jQuery(document).ready(function($) {
    $('#cac_pro_access').on('change', function() {
        if ($(this).val() === 'private') {
            $('#cac_pro_roles_row').show();
        } else {
            $('#cac_pro_roles_row').hide();
        }
    });
});
</script>
