<?php

echo 123;

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Генератор кнопок для группы';

require_once(dirname(__FILE__).'/template/top.php');

if (!$adm_info['root'])
{
    echo '<p>У вас нет доступа к этому модулю.</p>';
    
    exit;
}

$gId=0;

if (isset ($_GET['gId']))
  $gId=$_GET['gId'];
  


if (!$gId)
{
   echo 'No group specified. Error!';
   
   require_once('template/bottom.php');
   
   exit;
}



   
$fn=0;

$params['group_id'] = $gId;
$result=_vkApi_call('groups.getById', $params);

echo "
<h1>Кнопки для группы \"<a href=\"https://vk.com/club{$gId}\" target=\"_vk\">{$result[0]['name']}</a>\" на основе общего кода</h1>";


$sql=mysqli_query($dblink,"SELECT vkGroupPHP FROM `".DBP."vkApi` WHERE vkGroupId={$gId}");
list($vkGroupPHP)=mysqli_fetch_array($sql);



eval ($vkGroupPHP);

if(!isset($buttons[0]))
    $buttons=$buttons_reg;


foreach ($buttons as $i=>$but)
{
    //print_r($but);
    foreach($but as $j=>$b)
    {
        
        
        //*
        echo "<br><br>
        \$buttons[{$i}][{$j}][0][\"payload\"]=\"{$b[0]['payload']}\";<br>
        \$buttons[{$i}][{$j}][1]=\"{$b[1]}\";<br>
        \$buttons[{$i}][{$j}][2]=\"{$b[2]}\";<br>
        ";//*/
    }
}



require_once(dirname(__FILE__).'template/bottom.php');

?>
