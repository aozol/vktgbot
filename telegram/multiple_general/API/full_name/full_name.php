<?php

require_once (dirname(__FILE__).'/../../conf.php');



function full_name($name,$sex=0){

    GLOBAL $dblink;
    if(!$dblink)
    {
        $dblink=mysqli_connect(DB_SERVER, DB_LOGIN, DB_PASS);
        if (!$dblink) { echo 'Ошибка подключения к базе'; exit; }
        mysqli_query($dblink,"SET NAMES 'utf8'"); //на всякий случай, бывают проблемы с русскими буквами
        mysqli_select_db($dblink,DB_NAME);

    }
    

    $sql=mysqli_query($dblink,"SELECT full FROM `full_names` WHERE short='$name' AND sex=$sex");
    $sql2=mysqli_query($dblink,"SELECT full FROM `full_names` WHERE short='$name' AND sex=0");

    if (list ($fname)=mysqli_fetch_array($sql))
    {
        
    }

    elseif (($sex) AND (mysqli_num_rows($sql2)))
    {
        mysqli_query($dblink,"INSERT INTO `full_names`(short,full,sex) VALUES ('$name','$name','$sex')");
        list ($fname)=mysqli_fetch_array($sql2);
    }

    else
    {
        mysqli_query($dblink,"INSERT INTO `full_names`(short,full,sex) VALUES ('$name','$name','$sex')");
        $fname=$name;
    }

    return $fname;

}


?>
