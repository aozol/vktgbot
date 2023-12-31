<?

if(!defined('DBP_CITY'))
{

    define ('DBP_CITY', 'city_kit_');
    define ('CITY_ROWS_PER_PAGE', 6);
    define ('CITY_COLS', 2);
    
}



$defaultText['city_kit_start']= 'Пожалуйста, выберите страну, где вы находитесь, из вариантов на кнопках ниже (или добавьте новую, если вашей среди вариантов нет).';
$defaultText['city_kit_sel_country']= 'Пожалуйста, выберите страну, где вы находитесь, из вариантов на кнопках ниже (или добавьте новую, если вашей среди вариантов нет).';
$defaultText['city_kit_set_country']=$defaultText['city_kit_add_country']='Ваша страна установлена: %countryName%
Если вы ошиблись, нажмите кнопку "Изменить страну".

Если страна указана верно, выберите город из вариантов на кнопках ниже (или добавьте новый, если вашего города среди вариантов нет).

ВАЖНО! Если вы в небольшом городе, то советуем выбрать ближайший крупный, т.к. в этом случае вы присоединитесь к более крупному сообществу.';

$defaultText['city_kit_sel_city']= 'Выберите город из вариантов на кнопках ниже (или добавьте новый, если вашего города среди вариантов нет).

ВАЖНО! Если вы в небольшом городе, то советуем выбрать ближайший крупный, т.к. в этом случае вы присоединитесь к более крупному сообществу.';

$defaultText['city_kit_set_city']=$defaultText['city_kit_add_city']= 'Ваш город установлен: %cityName%
Если вы ошиблись, нажмите кнопку "Изменить город".';

$defaultText['city_kit_new_country']= 'На данный момент в системе присутствуют следующие страны:
%countryList%

Если ваша страна пребывания есть в этом списке, нажмите кнопку "Выбрать из списка" во избежание создания дубликатов.
Если страна в списке отсутствует, то пришлите ее название в ответном сообщении.';

$defaultText['city_kit_new_city']= 'На данный момент в системе присутствуют следующие города:
%cityList%

Если ваш город пребывания есть в этом списке, нажмите кнопку "Выбрать из списка" во избежание создания дубликатов.
Если город в списке отсутствует, то пришлите его название в ответном сообщении.

ВАЖНО! Если вы в небольшом городе, то советуем не создавать отдельный, а выбрать или ввести ближайший крупный, т.к. в этом случае вы присоединитесь к более крупному сообществу.';


?>
