<?php
/**
 * @package Albacross
 * @version 1.4.1
 */

define("ALBACROSS_PLUGIN_VERSION", "1.4.1");

function albacross_insert_code() {
  $client_id = get_option('albacross_client_id');

  if (empty($client_id)) {
    return;
  }

?>
<script>window._nQc="<?php echo esc_js(trim($client_id));?>";</script>
<script>window._nQs="WordPress-Plugin";</script>
<script>window._nQsv="<?php echo esc_js(ALBACROSS_PLUGIN_VERSION); ?>";</script>
<script async src="https://serve.albacross.com/track.js"></script>
<?php
}
