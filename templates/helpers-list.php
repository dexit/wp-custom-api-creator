<div class="cac-pro-helpers-sidebar">
    <ul>
        <?php foreach (array_keys($this->helpers) as $helper_name): ?>
            <li><code><?php echo esc_html($helper_name); ?>()</code></li>
        <?php endforeach; ?>
    </ul>
</div>
