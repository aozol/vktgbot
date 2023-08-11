<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


if(!defined('DB_NAME'))
{
    //адрес главной страницы скрипта
    define ('BOT_HOST', 'vkbot.aozol.ru/telegram/shutkobot/');
    define ('BOT_FOLDER', 'shutkobot');
    define ('GENERAL_DIR', dirname(__FILE__).'/../multiple_general/');
    define ('SHUTKOBOT_DIR', dirname(__FILE__).'/../shutkobot_general/');
    
    // Данные для подключения к базе
    require_once(GENERAL_DIR.'db_connect_conf.php');
    define ('DBP', 'tgbot_shutkobot_');

    define ('TEMPLATE_DIR', '/admin_template/');

    define ('ADM_VK_ID', 2204686);
    define ('BOT_LOGS_DIRECTORY', 'logs');
    define ('DEF_BOT_ID', 1903614144);
    define ('SUPPORT_TG_ID', "238009339");
}


//кнопки по умолчанию
$i=0;
$j=0;
$buttons_def[$i][$j][0]["payload"]="shutkobot_give_finish";
$buttons_def[$i][$j][1]='Добить';
$buttons_def[$i][$j][2]='primary';

$j++;
$buttons_def[$i][$j][0]["payload"]="shutkobot_vote";
$buttons_def[$i][$j][1]='Голосовать';
$buttons_def[$i][$j][2]='primary';

$j++;
$buttons_def[$i][$j][0]["payload"]="shutkobot_best";
$buttons_def[$i][$j][1]='Смотреть лучшие';
$buttons_def[$i][$j][2]='primary';

$i++; $j=0;
$buttons_def[$i][$j][0]["payload"]="shutkobot_my";
$buttons_def[$i][$j][1]='Мои шутки';
$buttons_def[$i][$j][2]='primary';

$j++;
$buttons_def[$i][$j][0]["payload"]="shutkobot_give_start";
$buttons_def[$i][$j][1]='Предложить зачин';
$buttons_def[$i][$j][2]='primary';
        
$i++; $j=0;
$buttons_def[$i][$j][0]["payload"]="shutkobot_share";
$buttons_def[$i][$j][1]='Пригласить друга';
$buttons_def[$i][$j][2]='default';

$j++;
$buttons_def[$i][$j][0]["payload"]="shutkobot_subscription";
$buttons_def[$i][$j][1]='Управление подпиской';
$buttons_def[$i][$j][2]='default';
//конец кнопок по умолчанию



$buttons_my[0][0][0]["payload"]="shutkobot_my_best";
$buttons_my[0][0][1]="Топ по среднему";
$buttons_my[0][0][2]="positive"; //primary, positive, negative
            
$buttons_my[0][1][0]["payload"]="shutkobot_my_best:medVote";
$buttons_my[0][1][1]="Топ по медиане";
$buttons_my[0][1][2]="positive"; //primary, positive, negative
            
$buttons_my[1][0][0]["payload"]="shutkobot_my_best";
$buttons_my[1][0][1]="Топ предыдущего голосования";
$buttons_my[1][0][2]="positive"; //primary, positive, negative

$buttons_my[2][0][0]["payload"]="shutkobot_my_edit";
$buttons_my[2][0][1]="Редактировать отправленные добивки";
$buttons_my[2][0][2]="positive"; //primary, positive, negative
//конец кнопок по умолчанию 

//данные отдельных шуткоботов
$botInfo[1903614144]['name']='Шуткобот';
$botInfo[1903614144]['tg']='shutko_bot';
$botInfo[1903614144]['vk']='shutkobot';

$buttons=array();
$attachments=array();

$service_token='0cc133310cc133310cc13331500caa5ea300cc10cc1333151db039ada7695f639c7bc80';
$client_secret='1mYVMxWTumjtkAxaCYnH';
$app_id='7040402';

?>
