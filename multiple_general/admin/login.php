<?

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

require_once(dirname(__FILE__).'/../conf.php');
require_once(dirname(__FILE__).'/../adm/functions.php');
require_once(dirname(__FILE__).'/../functions.php');

$auth=0;

if(isset($_COOKIE['vkId']))
{
    $my_hash=md5(VK_APP_ID.$_COOKIE['vkId'].VK_CLIENT_SECRET);
    
    if($_COOKIE['hash']==$my_hash)
        $auth=1;
    else
    {
        $auth=0;
        setcookie("vkId",$_GET['uid'],time()-3*24*3600);
        setcookie("hash",$_GET['hash'],time()-3*24*3600);
    }
    
}

elseif(isset($_GET['uid']))
{
    $my_hash=md5(VK_APP_ID.$_GET['uid'].VK_CLIENT_SECRET);
    
    if($_GET['hash']==$my_hash)
    {
        setcookie("vkId",$_GET['uid'],time()+3*24*3600);
        setcookie("hash",$_GET['hash'],time()+3*24*3600);
        $auth=1;
    }
    else
       $auth=0;
    
}

else
    $auth=0;
    
if(!$auth)
{

?>


<script type="text/javascript" src="https://vk.com/js/api/openapi.js?161"></script>
<script type="text/javascript">
  VK.init({apiId: <? echo VK_APP_ID; ?>});
</script>

<!-- VK Widget -->
<div id="vk_auth"></div>
<script type="text/javascript">
  VK.Widgets.Auth("vk_auth", {"authUrl":"/<?php echo BOT_FOLDER; ?>/admin/?f=login"});
</script>

<?

exit;
}

if ($auth AND isset($_GET['uid']))
    header('Location: index.php');
?>
