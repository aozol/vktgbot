<?php


if(!defined('DB_NAME')){
// Данные для подключения к базе
define ('DB_NAME','alexozol_aozo1');
define ('DB_SERVER','localhost');
define ('DB_LOGIN','alexozol_aozo1');
define ('DB_PASS','ozOl1Xol2');
define ('DBP', 'bot_shutkobot2_');

//адрес главной страницы скрипта
define ('BOT_HOST', 'vkbot.aozol.ru/shutkobot2/');
define ('GENERAL_DIR', dirname(__FILE__).'/../multiple_general/');

define ('ADM_VK_ID', 2204686);
define ('BOT_LOGS_DIRECTORY', 'logs');
}


//кнопки по умолчанию


$buttons_def[0][0][0]["payload"]="shutkobot_give_finish";
$buttons_def[0][0][1]='Добить';
$buttons_def[0][0][2]='primary';

$buttons_def[0][1][0]["payload"]="shutkobot_vote";
$buttons_def[0][1][1]='Голосовать';
$buttons_def[0][1][2]='primary';

$buttons_def[1][0][0]["payload"]="shutkobot_give_start";
$buttons_def[1][0][1]='Предложить зачин';
$buttons_def[1][0][2]='primary';

$buttons_def[2][0][0]["payload"]="shutkobot_best";
$buttons_def[2][0][1]='Смотреть лучшие';
$buttons_def[2][0][2]='primary';

$buttons_def[3][0][0]["payload"]="shutkobot_my";
$buttons_def[3][0][1]='Мои шутки';
$buttons_def[3][0][2]='primary';
        
$buttons_def[4][0][0]["payload"]="shutkobot_share";
$buttons_def[4][0][1]='Пригласить друга';
$buttons_def[4][0][2]='default';

$buttons_def[4][1][0]["payload"]="shutkobot_create";
$buttons_def[4][1][1]='Создать Шуткобота';
$buttons_def[4][1][2]='default';

$buttons_def[5][0][0]["payload"]="shutkobot_subscription";
$buttons_def[5][0][1]='Управление подпиской';
$buttons_def[5][0][2]='default';
//конец кнопок по умолчанию

$buttons_vote[0][0][0]["payload"]="shutkobot_vote:1";
$buttons_vote[0][0][1]='1';
$buttons_vote[0][0][2]='positive';

$buttons_vote[0][1][0]["payload"]="shutkobot_vote:3";
$buttons_vote[0][1][1]='3';
$buttons_vote[0][1][2]='positive';

$buttons_vote[0][2][0]["payload"]="shutkobot_vote:5";
$buttons_vote[0][2][1]='5';
$buttons_vote[0][2][2]='positive';

$buttons_vote[1][0][0]["payload"]="shutkobot_vote:8";
$buttons_vote[1][0][1]='8';
$buttons_vote[1][0][2]='positive';

$buttons_vote[1][1][0]["payload"]="shutkobot_vote:13";
$buttons_vote[1][1][1]='13';
$buttons_vote[1][1][2]='positive';

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

$buttons=array();        



//
$service_token='bccf2952bccf2952bccf2952e8bca0ceecbbccfbccf2952e29b21fa272490169098f258';//0cc133310cc133310cc13331500caa5ea300cc10cc1333151db039ada7695f639c7bc80
$client_secret='L0YF6HHKtjNZorCnGf5X';//1mYVMxWTumjtkAxaCYnH
$app_id='7333822'; //7040402

?>
