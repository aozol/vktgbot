<?php

$path='../../vk-campaign/'; //путь до папки со скриптом отправки

$max_t=19; //max_execution_time
$max_n=15; //максимальное количество сообщений с одного аккаунта в сессию (раз в 12 часов)
$sleep_t=100000; // в милисекундах //$min_sleep=1; $max_sleep=3; //границы интервала ожидания перед следующей отправкой
$n_once=2; //сколько отправить сообщений за один запуск скрипта

?>