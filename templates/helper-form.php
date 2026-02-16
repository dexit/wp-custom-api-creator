<div class="wrap">
    <h1><?php echo $helper_id ? __('Edit Helper', 'cac-pro') : __('Add New Helper', 'cac-pro'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('cac_pro_save_helper', 'cac_pro_helper_nonce'); ?>
        <input type="hidden" name="helper_id" value="<?php echo esc_attr($helper_id); ?>">

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Helper Name', 'cac-pro'); ?></th>
                <td>
                    <input type="text" name="helper_name" value="<?php echo esc_attr($helper['name']); ?>" class="regular-text" required>
                    <p class="description"><?php _e('Must start with cac_pro_helper_', 'cac-pro'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Description', 'cac-pro'); ?></th>
                <td>
                    <textarea name="helper_description" class="regular-text"><?php echo esc_textarea($helper['description']); ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Code', 'cac-pro'); ?></th>
                <td>
                    <textarea name="helper_code" id="helper_code"><?php echo esc_textarea($helper['code']); ?></textarea>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
<script>
jQuery(document).ready(function($) {
    if (typeof wp !== 'undefined' && wp.codeEditor) {
        wp.codeEditor.initialize($('#helper_code'), {
            codemirror: {
                mode: 'php',
                lineNumbers: true
            }
        });
    }
});
</script>
