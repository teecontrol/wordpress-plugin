<div class="wrap">
    <h1><?php esc_html_e('Teecontrol', 'teecontrol') ?></h1>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="teecontrol_settings">
        <?php wp_nonce_field('teecontrol_settings_verify'); ?>
        <?php
            do_settings_sections('teecontrol');
    submit_button(__('Save', 'general'));
    ?>
    </form>
</div>