<?php

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Список репостов';

if (isset ($_POST['post']))
{
      setcookie("reposts_token",$_POST['token'],time()+365*24*3600);
      setcookie("reposts_post",$_POST['post'],time()+30*24*3600);
      setcookie("reposts_excludeList",$_POST['excludeList'],time()+30*24*3600);
}


require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['post']))  //Обработчик сохранения формы
{
   
  $token=$_POST['token'];
  

  
  $post_str=preg_replace('#.*wall#','',$_POST['post']);
  $post_str=str_replace('%2Fall','',$post_str);
  list($params['owner_id'],$params['post_id'])=explode('_',$post_str);
  $params['count']=1000;
  
  setcookie("reposts_post",$post_str,time()+30*24*3600);
  
  $responce=_vkApi_call('wall.getReposts', $params);
  
  setcookie("reposts_excludeList",$_POST['excludeList'],time()+30*24*3600);
  
  
  $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."db` WHERE mlistId!={$_POST['excludeList']}");
             while (list($vkId)=mysqli_fetch_array($sql))
                $vkId_list[]=$vkId;
      $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."db` WHERE mlistId={$_POST['excludeList']}");
             while (list($vkId)=mysqli_fetch_array($sql))
                $vkId_list_excl[]=$vkId;
  
  foreach ($responce['items'] as $r)
    if ($r['to_id']>0) if (in_array($r['to_id'],$vkId_list) AND !in_array($r['to_id'],$vkId_list_excl)) echo "<a href=\"https://vk.com/id{$r['to_id']}\" target=\"_vk\">https://vk.com/id{$r['to_id']}</a><br>";
  
}

$lists='<option value="-5000">Не исключать';


$sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;

foreach ($mlistName as $k=>$v)
{
  
  if(isset($_POST['excludeList']) AND ($_POST['excludeList']==$k))
    $lists.='<option value="'.$k.'" selected>'.$v;

  else
    $lists.='<option value="'.$k.'">'.$v;
  
}


?>

<form action="" method="post">

<p>Токен пользователя (например, можно получить <a href="https://oauth.vk.com/authorize?client_id=2685278&scope=friends,photos,audio,video,docs,notes,pages,status,offers,questions,wall,groups,messages,notifications,stats,ads,market,offline&redirect_uri=https://api.vk.com/blank.html&display=page&response_type=token&revoke=1&confirm=1" target="_token">по ссылке</a>): <input type="text" name="token" value="<?php if(isset($_COOKIE['reposts_token'])) echo $_COOKIE['reposts_token']; ?>" /></p>
<p>Ссылка на пост: <input type="text" name="post" value="<?php if(isset($_COOKIE['reposts_post'])) echo $_COOKIE['reposts_post']; ?>" /></p>
<p>Исключить подписчиков списка: <select name="excludeList"><?php if(isset($_COOKIE['reposts_excludeList'])) echo str_replace("\"{$_COOKIE['reposts_excludeList']}\"","\"{$_COOKIE['reposts_excludeList']}\" selected",$lists); else echo $lists; ?></textarea></p>
<input type="submit" value="Посмотреть" /><br></p>
</form>

<?




require_once(dirname(__FILE__).'template/bottom.php');

?>
