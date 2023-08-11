<?

require_once(dirname(__FILE__).'/functions.php');

$city_buttons=array();

if(isset($replyText))
{
    if (!$replyText)
        $replyText=$defaultText[$payloadText];
}
else
    $replyText=$defaultText[$payloadText];




switch ($payloadText)
{

    case 'city_kit_start':
        
        $n=CITY_ROWS_PER_PAGE*CITY_COLS;
        $start=0;
        
        list($items,$itemsN)=city_kit_list('country',$start,$n);
        
        //bot_debugger($itemsN);
        
        $s=0;
        foreach($items as $item)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_set_country:".$item[0];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]=$item[1];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
            
            //bot_debugger($item[1]);

        }
        
        if ($itemsN>$n)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:1";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]="Следующие страны";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
        
        else
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_new_country";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]="Другая страна";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
            
        
    break;
    
    case 'city_kit_sel_country':
        
        $n=CITY_ROWS_PER_PAGE*CITY_COLS;
        $start=$payloadParams*$n;
        
        list($items,$itemsN)=city_kit_list('country',$start,$n);
        
        $s=0;
        foreach($items as $item)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_set_country:".$item[0];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]=$item[1];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
        
        if($start)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:".($payloadParams-1);
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Предыдущие страны';
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
        if ($itemsN>$n+$start)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:".($payloadParams+1);
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Следующие страны';
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
        else
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_new_country";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]="Другая страна";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
    
    break;

    
    case 'city_kit_add_country':
        
        $countryName=$incommingText;
        $payloadParams=city_kit_check_country($countryName);
        

    
    case 'city_kit_set_country':
        
        $itemId=$payloadParams;
        
        city_kit_set_country($vkId,$itemId);
        
        $replyText=str_replace('%countryName%',city_kit_country_name($itemId),$replyText);
        
        $s=0;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Изменить страну';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
        
        $n=(CITY_ROWS_PER_PAGE-1)*CITY_COLS;
        $start=0;
        
        list($items,$itemsN)=city_kit_list('city',$start,$n,$itemId);
        
        $s=CITY_COLS;
        foreach($items as $item)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_set_city:".$item[0];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]=$item[1];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
        
        //bot_debugger("$itemsN>$n+$start");
        
        if ($itemsN>$n+$start)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_city:1";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Следующие города';
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
        else
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_new_city";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]="Другой/новый город";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
    
    break;
    
    case 'city_kit_sel_city':
        
        $n=CITY_ROWS_PER_PAGE*CITY_COLS;
        $start=$payloadParams*$n;
        
        list($countryId,$cityId)=city_kit_userCityId($vkId);
        
        list($items,$itemsN)=city_kit_list('city',$start,$n,$countryId);
        
        $s=0;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Изменить страну';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
        
        $s=CITY_COLS;
        foreach($items as $item)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_set_city:".$item[0];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]=$item[1];
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='positive';
            $s++;
        }
        
        if($start)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_city:".($payloadParams-1);
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Предыдущие города';
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
        if ($itemsN>$n+$start)
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_city:".($payloadParams+1);
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Следующие города';
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
        else
        {
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_new_city";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]="Другой/новый город";
            $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
            $s++;
        }
        
    
    break;
    
    
    case 'city_kit_add_city':
        
        list($countryId,$cityId)=city_kit_userCityId($vkId);
        $cityName=$incommingText;
        $payloadParams=city_kit_check_city($cityName,$countryId);
    
    case 'city_kit_set_city':
        $itemId=$payloadParams;
        
        city_kit_set_city($vkId,$itemId);
        
        $replyText=str_replace('%cityName%',city_kit_city_name($itemId),$replyText);
        
        $s=0;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_city:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Изменить город';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
    
    break;
    
    case 'city_kit_new_country':
        
        $userState='city_kit_add_country';
        
        list($items,$itemsN)=city_kit_list('country',0,0);
        
        $items_str='';
        
        foreach($items as $item)
            $items_str.=$item[1].'
';
        
        if(!$items_str)
            $items_str='(пусто)';
            
        $replyText=str_replace('%countryList%',$items_str,$replyText);
        
        $s=0;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Выбрать из списка';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
        
        
    
    break;
    
    case 'city_kit_new_city':
        
        $userState='city_kit_add_city';
        
        list($countryId,$cityId)=city_kit_userCityId($vkId);
        list($items,$itemsN)=city_kit_list('city',0,0,$countryId);
        
        $items_str='';
        
        foreach($items as $item)
            $items_str.=$item[1].'
';
        if(!$items_str)
            $items_str='(пусто)';
        
        $replyText=str_replace('%cityList%',$items_str,$replyText);
        
        $s=0;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_city:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Выбрать из списка';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
        
        $s++;
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][0]["payload"]="city_kit_sel_country:0";
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][1]='Изменить страну';
        $city_buttons[$s/CITY_COLS][$s%CITY_COLS][2]='primary';
        
    
    break;

    
    case 'city_kit_':
    
    break;


}

$buttons=array_merge($city_buttons,$buttons);

?>
