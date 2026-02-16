<div class="wrap">
    <h1>
        <?php _e('Helper Functions', 'cac-pro'); ?>
        <a href="<?php echo admin_url('admin.php?page=cac-pro-helpers&action=add'); ?>" class="page-title-action"><?php _e('Add New', 'cac-pro'); ?></a>
    </h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Name', 'cac-pro'); ?></th>
                <th><?php _e('Description', 'cac-pro'); ?></th>
                <th><?php _e('Actions', 'cac-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($helpers)): ?>
                <tr>
                    <td colspan="3"><?php _e('No helpers found.', 'cac-pro'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($helpers as $helper): ?>
                    <tr>
                        <td><strong><?php echo esc_html($helper->name); ?></strong></td>
                        <td><?php echo esc_html($helper->description); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=cac-pro-helpers&action=edit&helper=' . $helper->id); ?>"><?php _e('Edit', 'cac-pro'); ?></a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=cac-pro-helpers&action=delete&helper=' . $helper->id), 'cac_pro_delete_helper'); ?>" class="submitdelete" onclick="return confirm('<?php _e('Are you sure?', 'cac-pro'); ?>')"><?php _e('Delete', 'cac-pro'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
