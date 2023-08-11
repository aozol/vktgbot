<?php
require_once('../conf.php');

if (isset ($_GET['f']))
   require_once(GENERAL_DIR.'adm/'.$_GET['f'].'.php');
else
   require_once(GENERAL_DIR.'adm/index.php');

?>
