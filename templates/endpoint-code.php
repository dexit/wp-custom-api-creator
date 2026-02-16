<div class="cac-pro-code-editor">
    <textarea id="cac_pro_code" name="cac_pro_code"><?php echo esc_textarea($code); ?></textarea>
    <p class="description">
        <?php _e('Available variables: $params, $headers, $method, $body, $user.', 'cac-pro'); ?><br>
        <?php _e('Use cac_pro_helper_name() to call helper functions.', 'cac-pro'); ?>
    </p>
</div>

<div class="cac-pro-json-configs" style="margin-top: 20px;">
    <h3><?php _e('Parameters Configuration (JSON)', 'cac-pro'); ?></h3>
    <textarea name="cac_pro_params" class="widefat" rows="5"><?php echo esc_textarea(json_encode($params, JSON_PRETTY_PRINT)); ?></textarea>

    <h3><?php _e('Response Structure (JSON)', 'cac-pro'); ?></h3>
    <textarea name="cac_pro_response" class="widefat" rows="5"><?php echo esc_textarea(json_encode($response, JSON_PRETTY_PRINT)); ?></textarea>
</div>
