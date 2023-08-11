<?

require_once(dirname(__FILE__).'/conf.php');

if (!$dblink)
{
$dblink=mysqli_connect(DB_SERVER, DB_LOGIN, DB_PASS);
  if (!$dblink) { echo 'Ошибка подключения к базе'; exit; }
  mysqli_query($dblink,"SET NAMES 'utf8'"); //на всякий случай, бывают проблемы с русскими буквами
  mysqli_select_db($dblink,DB_NAME);
}


function city_kit_check_country($countryName)
{
    GLOBAL $dblink;
    $sql=mysqli_query($dblink,"SELECT countryId FROM `".DBP_CITY."countries` WHERE countryName='{$countryName}'");
    if(list($countryId)=mysqli_fetch_array($sql))
        return $countryId;
    
    else
        return city_kit_add_country($countryName);

}

function city_kit_check_city($cityName,$countryId)
{
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT cityId FROM `".DBP_CITY."cities` WHERE cityName='{$cityName}' AND countryId={$countryId}");
    if(list($cityId)=mysqli_fetch_array($sql))
        return $cityId;
    
    else
        return city_kit_add_city($cityName,$countryId);
}

function city_kit_add_country($countryName)
{
    GLOBAL $dblink;
    
    if (mysqli_query($dblink,"INSERT INTO `".DBP_CITY."countries` (countryName) VALUES ('{$countryName}')"))
        return mysqli_insert_id($dblink);
    else
        return FALSE;
    
}

function city_kit_add_city($cityName,$countryId)
{
    GLOBAL $dblink;
    
    if (mysqli_query($dblink,"INSERT INTO `".DBP_CITY."cities` (cityName,countryId) VALUES ('{$cityName}',{$countryId})"))
        return mysqli_insert_id($dblink);
    else
        return FALSE;
    
}

function city_kit_list($type='country',$start=0,$n=0,$countryId=0)
{
    GLOBAL $dblink;
    
    if (!$n)
        $n=1000000000000;
    else
        $n+=$start;
    
    switch ($type)
    {
    
        case 'country':
            $query1="SELECT countryId,countryName FROM `".DBP_CITY."countries` LIMIT {$start},{$n}";
            $query2="SELECT COUNT(countryId) FROM `".DBP_CITY."countries`";
        break;
        
        case 'city':
            $query1="SELECT cityId,cityName FROM `".DBP_CITY."cities` WHERE countryId={$countryId} LIMIT {$start},{$n}";
            $query2="SELECT COUNT(cityId) FROM `".DBP_CITY."cities` WHERE countryId={$countryId}";
        break;
        
    }
    
    $sql=mysqli_query($dblink,$query1);
    
    $items=array();
    $i=0;
    while(list($Id,$Name)=mysqli_fetch_array($sql))
    {
        $items[$i][0]=$Id;
        $items[$i][1]=$Name;
        $i++;
    }
    
    $sql=mysqli_query($dblink,$query2);
    
    list($N)=mysqli_fetch_array($sql);
    
    return array($items,$N);
    
}

function city_kit_set_country($vkId,$countryId)
{
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP_GENERAL."userCity` WHERE vkId={$vkId}");
    
    if (mysqli_num_rows($sql))
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."userCity` SET countryId={$countryId}, cityId=NULL WHERE vkId={$vkId}");
    else
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userCity` (vkId,countryId) VALUES ({$vkId},{$countryId})");
    
    
}

function city_kit_set_city($vkId,$cityId)
{
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT countryId FROM `".DBP_CITY."cities` WHERE cityId={$cityId}");
    
    list($countryId)=mysqli_fetch_array($sql);
    
    $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP_GENERAL."userCity` WHERE vkId={$vkId}");
    
    if (mysqli_num_rows($sql))
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."userCity` SET countryId={$countryId}, cityId={$cityId} WHERE vkId={$vkId}");
    else
        mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userCity` (vkId,countryId,cityId) VALUES ({$vkId},{$countryId},{$cityId})");
    
    
}



function city_kit_country_name($countryId)
{
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT countryName FROM `".DBP_CITY."countries` WHERE countryId={$countryId}");
    
    list($countryName)=mysqli_fetch_array($sql);
    
    return $countryName;
}

function city_kit_city_name($cityId)
{
    GLOBAL $dblink;
    
    $sql=mysqli_query($dblink,"SELECT cityName FROM `".DBP_CITY."cities` WHERE cityId={$cityId}");
    
    list($cityName)=mysqli_fetch_array($sql);
    
    return $cityName;
    
}

function city_kit_userCityId($vkId)
{
    GLOBAL $dblink;
    
     $sql=mysqli_query($dblink,"SELECT countryId,cityId FROM `".DBP_GENERAL."userCity` WHERE vkId={$vkId}");
    
    if (list($countryId,$cityId)=mysqli_fetch_array($sql))
        return array($countryId,$cityId);
    else
        return array(NULL,NULL);
    
}

?>
