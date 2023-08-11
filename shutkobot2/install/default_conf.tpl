<?php


if(!defined('DB_NAME')){
// Данные для подключения к базе
define ('DB_NAME','');
define ('DB_SERVER','');
define ('DB_LOGIN','');
define ('DB_PASS','');
define ('DBP', '%prefix%');

//адрес главной страницы скрипта
define ('BOT_HOST', '%domain%/%dirname%/');
define ('GENERAL_DIR', dirname(__FILE__).'/../multiple_general/');

define ('ADM_VK_ID', %vk_admin%);
define ('BOT_LOGS_DIRECTORY', 'logs');
}


//кнопки по умолчанию
        
%def_buttons%
//конец кнопок по умолчанию       

$buttons=array();
$attachments=array();


?> 
