<?php
require_once (dirname(__FILE__).'/full_name.php');

echo full_name($_GET['name'],$_GET['sex']);
?>
