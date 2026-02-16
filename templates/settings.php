<div class="wrap">
    <h1><?php _e('Custom API Pro Settings', 'cac-pro'); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('cac_pro_settings'); ?>
        <input type="hidden" name="cac_pro_settings" value="1">

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Enable Cache', 'cac-pro'); ?></th>
                <td>
                    <input type="checkbox" name="enable_cache" <?php checked($this->settings['enable_cache']); ?>>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Cache Duration (seconds)', 'cac-pro'); ?></th>
                <td>
                    <input type="number" name="cache_duration" value="<?php echo esc_attr($this->settings['cache_duration']); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Enable Logging', 'cac-pro'); ?></th>
                <td>
                    <input type="checkbox" name="enable_logging" <?php checked($this->settings['enable_logging']); ?>>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Log Level', 'cac-pro'); ?></th>
                <td>
                    <select name="log_level">
                        <option value="debug" <?php selected($this->settings['log_level'], 'debug'); ?>>Debug</option>
                        <option value="info" <?php selected($this->settings['log_level'], 'info'); ?>>Info</option>
                        <option value="warning" <?php selected($this->settings['log_level'], 'warning'); ?>>Warning</option>
                        <option value="error" <?php selected($this->settings['log_level'], 'error'); ?>>Error</option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
