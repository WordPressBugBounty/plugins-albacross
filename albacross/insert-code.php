<?php
/**
 * @package Albacross
 * @version 1.3.3
 */

function albacross_insert_code() {
  $client_id = get_option('albacross_client_id');

  if (empty($client_id)) {
    return;
  }

?>
<script type="text/javascript">
  _nQc = '<?php echo trim($client_id);?>';
  _nQs = 'WordPress-Plugin';
  _nQsv = '1.3.1';
  _nQt = new Date().getTime();
  (function() {
    var no = document.createElement('script'); no.type = 'text/javascript'; no.async = true;
    no.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'serve.albacross.com/track.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(no, s);
  })();
</script>
<?php
}
