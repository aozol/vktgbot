<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/adm/functions.php');


$sql=mysqli_query($dblink,"SELECT finLink,finDate,activationsN,activatedN,actionPHP FROM `".DBP."redirects` WHERE redirId='{$_GET['redirId']}'");

//echo "SELECT finLink,finDate,activationsN,activatedN,actionPHP FROM `".DBP."redirects` WHERE redirId=''";

list($finLink,$finDate,$activationsN,$activatedN,$actionPHP)=mysqli_fetch_array($sql);


if(strtotime($finDate)<time())
{
    echo 'The link is expired<br>';
    echo '<br>Now is '.time().' but expiration is '.$finDate;
   
}

if($activationsN)
{
    if($activatedN>=$activationsN)
    {
        echo 'The activations limit exceded';
        exit;
    }
}

$vkId=$_GET['vkId'];
$currentGroupId=$_GET['botId'];

eval ($actionPHP);

header("Location: {$finLink}");

?>
