<div style="position: relative; top: 0px; left: 0px; padding: 15px; width: 100%; height: 50px;">&nbsp;</div>

<div style="position: fixed; top: 0px; left: 0px; background: blue; opacity: 0.9; color: white; padding: 15px; width: 100%">

<?php
if (isset($form)) echo $form;
if(strstr($_SERVER['REQUEST_URI'],'adm'))
{
  echo '<a href="http://'.BOT_HOST.'adm/" class="menu">Главная страница настроек</a> <a href="index.php?f=message_to_list" class="menu">Отправить рассылку подписчикам</a> <a href="http://'.BOT_HOST.'/updater.php" target="_update" class="menu">Ручной запуск скрипта обновления зачина</a> <a href="index.php?f=emulate" class="menu">Эмулировать запарос от пользователя</a>';

}
else
{
  echo '<a href="http://'.BOT_HOST.'adm/" class="menu">Перейти к настройкам</a>';
}

?>
</div>
