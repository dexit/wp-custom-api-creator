<div class="wrap">
    <h1><?php _e('Custom API Pro Dashboard', 'cac-pro'); ?></h1>
    <p><?php _e('Welcome to Custom API Pro. Use the menu on the left to manage your API endpoints and helper functions.', 'cac-pro'); ?></p>

    <div class="card">
        <h2><?php _e('Quick Links', 'cac-pro'); ?></h2>
        <ul>
            <li><a href="<?php echo admin_url('edit.php?post_type=cac_api'); ?>"><?php _e('Manage Endpoints', 'cac-pro'); ?></a></li>
            <li><a href="<?php echo admin_url('post-new.php?post_type=cac_api'); ?>"><?php _e('Add New Endpoint', 'cac-pro'); ?></a></li>
            <li><a href="<?php echo admin_url('admin.php?page=cac-pro-helpers'); ?>"><?php _e('Helper Functions', 'cac-pro'); ?></a></li>
            <li><a href="<?php echo admin_url('admin.php?page=cac-pro-settings'); ?>"><?php _e('Settings', 'cac-pro'); ?></a></li>
        </ul>
    </div>
</div>
