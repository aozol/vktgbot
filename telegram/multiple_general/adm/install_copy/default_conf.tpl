<?php


if(!defined('DB_NAME'))
{
    //адрес главной страницы скрипта
    define ('BOT_HOST', '%domain%/telegram/%dirname%/');
    define ('BOT_FOLDER', '%dirname%');
    define ('GENERAL_DIR', dirname(__FILE__).'/../multiple_general/');

    // Данные для подключения к базе
    require_once(GENERAL_DIR.'db_connect_conf.php');
    define ('DBP', '%prefix%');

    define ('TEMPLATE_DIR', '/admin_template/');

    define ('ADM_TG_ID', %vk_admin%);
    define ('SUPPORT_TG_ID', %vk_support%);
    define ('BOT_LOGS_DIRECTORY', 'logs');
    define ('DEF_BOT_ID', %def_bot_id%);
}


//кнопки по умолчанию
        
%def_buttons%
//конец кнопок по умолчанию       

$buttons=array();
$attachments=array();


?>
