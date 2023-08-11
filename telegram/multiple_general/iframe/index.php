<?php

/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//*/

$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$client_secret = 'ejyB4vObzox9xJD3Sgcv'; //Защищённый ключ из настроек вашего приложения

$query_params = [];
parse_str(parse_url($url, PHP_URL_QUERY), $query_params); // Получаем query-параметры из URL

$sign_params = [];
foreach ($query_params as $name => $value)
{
    if (strpos($name, 'vk_') !== 0) { // Получаем только vk параметры из query
    continue;
}
        
$sign_params[$name] = $value;
}

ksort($sign_params); // Сортируем массив по ключам
$sign_params_query = http_build_query($sign_params); // Формируем строку вида "param_name1=value&param_name2=value"
$sign = rtrim(strtr(base64_encode(hash_hmac('sha256', $sign_params_query, $client_secret, true)), '+/', '-_'), '='); // Получаем хеш-код от строки, используя защищеный ключ приложения. Генерация на основе метода HMAC.

$status = $sign === $query_params['sign']; // Сравниваем полученную подпись со значением параметра 'sign'

if(!$status)
{
    echo 'error';
    exit;
}





if(!isset($_GET['hash']))
{
    echo '<script type="text/javascript">
    var hash = window.location.hash, //get the hash from url
        cleanhash = hash.replace("#", "");

    location.replace("'.$url.'&hash=1&"+cleanhash);

    </script> ';
}

else
{

    require_once(dirname(__FILE__).'/../conf.php');
    require_once(dirname(__FILE__).'/../adm/functions.php');
    require_once(dirname(__FILE__).'/../functions.php');
    
    $page_title="Страница подписки";
    require_once(dirname(__FILE__).'/template/top.php');
    
    
    
    echo "Hi, {$_GET['vk_user_id']} from {$_GET['vk_group_id']}! Param1: {$_GET['param1']}, Param2: {$_GET['param2']}<br> ";
    
    echo '
    <p><a href="#param2=2" onclick="return ChangeColor(this);">Пример 1</a></p>
    

    
    ';
    



   require_once(dirname(__FILE__).'/template/bottom.php');

}
?>
