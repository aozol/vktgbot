<?php

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

$auth=0;

if(isset($_COOKIE['admlogin']))
{
   $login=$_COOKIE['admlogin'];
   $pass=$_COOKIE['admpass'];
}

elseif(isset($_POST['login']))
{
   $login=$_POST['login'];
   $pass=hash_pass($_POST['pass']);
}

else $login='';


if($login)
{ //если уже есть куки или отправлен логин - проверка/авторизация
   $user = mysqli_query($dblink,"SELECT * FROM `".DBP."admin` WHERE `login` = '".$login."'");
   
   $from_user=mysqli_fetch_assoc($user);
   if(!mysqli_num_rows($user))
     $auth=0;
   elseif($from_user['pass']!=$pass)
     $auth=0;



   else{
      setcookie("admlogin",$login,0x6FFFFFFF);
      setcookie("admpass",$pass,0x6FFFFFFF);
      $auth=1;
      $adm_info['root']=$from_user['root'];
      $adm_info['id']=$from_user['id'];
      
   }

}

if(!$auth)
{
   setcookie("admlogin",0,time() - 3600);
   setcookie("admpass",0,time() - 3600);
   
   require_once('template/top.php');
?>



<p>Вы не авторизованы, либо Ваш логин неактивен. Авторизуйтесь, используя форму ниже.</p>

<form action="" method="post">
<p>Логин:<br/><input name="login" type="text" value="<?php if (isset($_POST['login'])) echo $_POST['login']; ?>" size="20" /></p>
<p>Пароль:<br/><input name="pass" type="password" value="" size="20" /></p>
<p><input style="font-family: Verdana; font-size: 8pt; color: rgb(0, 0, 0); background-color: rgb(255, 190, 133); font-weight: bold;" type="submit" value="Войти" />
</form>

<p>Если Вы забыли пароль, обратитесь к администратору системы.</p>



<?php

   require_once('template/bottom.php');

   exit;
}



?>
