<?php


if(!defined('DB_NAME')){
// Данные для подключения к базе
define ('DB_NAME','alexozol_aozo1');
define ('DB_SERVER','localhost');
define ('DB_LOGIN','alexozol_aozo1');
define ('DB_PASS','ozOl1Xol2');
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
