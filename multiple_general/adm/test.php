<?php
require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Test';

require_once(dirname(__FILE__).'/template/top.php');

$fname = ;

if(is_file($fname)) echo filesize($fname);

                $conf_str = file_get_contents ('../conf.php');

                $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
                $_POST['buttons_php'].=$buttons_def_str;


require_once(dirname(__FILE__).'template/bottom.php');

?>



