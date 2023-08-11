<?php



require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='История сообщений и ответ';

require_once(dirname(__FILE__).'/template/top.php');

if (isset($_GET['bot_id']))
{
    $currentGroupId=$_GET['bot_id'];
    //bot_debugger('I know bot_id!!! '.$_GET['bot_id']);
}
elseif (isset($_GET['botId']))
    $currentGroupId=$_GET['botId'];
else
    $currentGroupId=DEF_BOT_ID;

$sql=mysqli_query($dblink,"SELECT token FROM `".DBP."vkApi` WHERE vkGroupId=".$currentGroupId);
list($token)=mysqli_fetch_array($sql);


if(isset($_POST['text'])){

    $vkGroupId[$_GET['vkId']]=$currentGroupId;
    $personalText=personal_text($_GET['vkId'],$_POST['text']);
    $sendResult=message_queue($token,$_GET['vkId'],$personalText[$_GET['vkId']],date('Y-m-d H:i:00'),$_POST['buttons_php']);
    
    echo "Сообщение поставлено в очередь и будет отправлено примерно в течение минуты. Чтобы отправить сейчас, перейдите <a href=\"https://vkbot.aozol.ru/telegram/multiple_general/cron/message_sender.php\">по ссылке</a>";
    



}

else
{

    if ($_GET['limit'])
    {
        $limit_str=" LIMIT {$_GET['limit']}";
    }

    else
        $limit_str=" LIMIT 20";

    echo '<form action="" method="post" target="_send">

    <p>Отправить ответное сообщение:</p>
    <table border="0">
    <tr><td>Текст</td><td>Кнопки бота</td><td>Подсказки</td></tr>

    <tr><td><textarea name="text" style="width: 300px; height: 200px"></textarea></td><td><textarea name="buttons_php" style="width: 300px; height: 200px"></textarea></td><td>%name% - имя человека (как указано в вк)<br/>
    %last_name% - фамилия человека (как указано в вк)<br/>
    %full_name% - полное имя человека (строится на основе имени из вк: Маша -> Мария)<br/>
    %t_hi% - Доброе утро/Добрый день/Добрый вечер/Доброй ночи - в зависимости от текущего времени суток<br/>
    %t_hi_small% - доброе утро/добрый день/добрый вечер/доброй ночи - с маленькой буквы, в зависимости от текущего времени суток<br/>
    {женское|мужское} - различные варианты в зависимости от пола. Поледовательность именно такая!</p>
    <p>Шаблон кнопки:<br/>
    $buttons[0][0][0]["payload"]="command";<br/>
    $buttons[0][0][1]="Описание на кнопке";<br/>
    $buttons[0][0][2]="default";<br/><br/></td></tr>
    </table>
    <p><input type="submit" value="Отправить"></p>
    </form><br><br>';    

    $sql=mysqli_query($dblink,"SELECT dateTime,messageText,sended,token FROM `".DBP_GENERAL."message_queue` WHERE vkId={$_GET['vkId']} AND token='{$token}' ORDER BY dateTime DESC {$limit_str}");

    $align[0]='right';
    $align[1]='right';
    $align[-1]='left';
    
    $color[0]='black';
    $color[1]='black';
    $color[-1]='blue';

    
    $user_info=vkApi_usersGet($_GET['vkId']);
    
    $who[0]='Мы';
    $who[1]='Мы';
    $who[-1]='<a href="https://t.me/'.$user_info[0]['username'].'" target="_vk">'.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].'</a>';

    while (list($dateTime,$messageText,$sended)=mysqli_fetch_array($sql))
    {
        echo "<p style=\"color: {$color[$sended]}\"><strong>{$who[$sended]} ({$dateTime}):</strong> <pre style=\"color: {$color[$sended]}\">$messageText</pre></p>";
    }


}


//*/
require_once(dirname(__FILE__).'/template/bottom.php');


?>
