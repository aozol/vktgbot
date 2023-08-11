<?php
require_once('../conf.php');

if (isset ($_GET['f']))
   require_once(GENERAL_DIR.'admin/'.$_GET['f'].'.php');
else
   require_once(GENERAL_DIR.'admin/index.php');

?>
