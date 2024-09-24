<?php
/**
 * @package Albacross
 * @version 1.3.3
 */

function albacross_admin_create_menu() {
  add_menu_page(__('Albacross Settings', 'albacross-wordpress-plugin'), 'Albacross', 'administrator', __FILE__, 'albacross_settings_page');
}

function albacross_register_settings() {
  register_setting('albacross-group-settings', 'albacross_client_id');
}

function albacross_admin_notice() {
  $client_id = get_option('albacross_client_id');
  if (!empty($client_id)) {
    return false;
  }

  echo '<div class="error">
    <p>' . __('You have not yet set your Albacross client ID', 'albacross-wordpress-plugin') . ': <a href="' . admin_url('admin.php?page=albacross%2Fadmin.php') . '">' . __('Update now', 'albacross-wordpress-plugin') . '</a></p>
  </div>';
}

function albacross_settings_page() {
?>
<div class="wrap">
<h2>Albacross</h2>

<p>Your <b>Client ID</b> can be found by signing in on <a href="https://app.albacross.com/" target="_blank">app.albacross.com</a> and going to your settings.</p>

<form method="post" action="options.php">
  <?php settings_fields('albacross-group-settings');?>
  <?php do_settings_sections('albacross-group-settings');?>
  <table class="form-table">
    <tr valign="top">
      <th scope="row"><?php echo __('Client ID', 'albacross-wordpress-plugin');?></th>
      <td>
          <input type="text" name="albacross_client_id" value="<?php echo esc_attr(get_option('albacross_client_id'));?>" placeholder="<?php echo __('Your client ID', 'albacross-wordpress-plugin');?>">
      </td>
    </tr>
  </table>
  <?php submit_button();?>
</form>
</div>
<?php
}
?>
