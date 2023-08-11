<?php


//Версия API
if(!defined('VK_API_VERSION')) {
  define('VK_API_VERSION', '5.131'); //Используемая версия API
  define('VK_API_ENDPOINT', 'https://api.vk.com/method/');
}

function vkApi_messagesSend($peer_id, $message, $attachments = array()) {
  return _vkApi_call('messages.send', array(
    'peer_ids'    => $peer_id,
    'message'    => $message,
    'attachment' => implode(',', $attachments),
    'random_id' => floor(microtime(1)*10000)
  ));
}
function vkApi_messagesSendButtons($peer_id, $message, $buttons_array, $attachments = array()) {

// 

        $buttons=array();
        $i = 0;
        foreach ($buttons_array as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                $color = $button[2];
                $buttons[$i][$j]["action"]["type"] = "text";
                if ($button[0] != null)
                    $buttons[$i][$j]["action"]["payload"] = json_encode($button[0], JSON_UNESCAPED_UNICODE);
                $buttons[$i][$j]["action"]["label"] = mb_substr($button[1],0,40);//$button[1]; //
                $buttons[$i][$j]["color"] = $color;
                $j++;
            }
            $i++;
        }
        $buttons = array(
            "one_time" => false,
            "buttons" => $buttons);
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);        
        
    log_keyboard($peer_id,$buttons_array);
    

  return _vkApi_call('messages.send', array(
    'peer_ids'    => $peer_id,
    'random_id' => floor(microtime(1)*10000),
    'message'    => $message,
    'attachment' => implode(',', $attachments),    
    'keyboard' => $buttons
  ));
}


function vkApi_messagesSend_now($user_ids, $message, $attachments = array()) {
  return _vkApi_call('messages.send', array(
    'user_ids'    => $user_ids,
    'message'    => $message,
    'attachment' => implode(',', $attachments)
  ));
}
function vkApi_messagesSendButtons_now($user_ids, $message, $buttons_array, $attachments = array()) {

// 

        $buttons=array();
        $i = 0;
        foreach ($buttons_array as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                $color = $button[2];
                $buttons[$i][$j]["action"]["type"] = "text";
                if ($button[0] != null)
                    $buttons[$i][$j]["action"]["payload"] = json_encode($button[0], JSON_UNESCAPED_UNICODE);
                $buttons[$i][$j]["action"]["label"] = mb_substr($button[1],0,40);//$button[1]; //
                $buttons[$i][$j]["color"] = $color;
                $j++;
            }
            $i++;
        }
        $buttons = array(
            "one_time" => false,
            "buttons" => $buttons);
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);        
        
    log_keyboard($user_ids,$buttons_array);

  return _vkApi_call('messages.send', array(
    'user_ids'    => $user_ids,
    'message'    => $message,
    'attachment' => implode(',', $attachments),    
    'keyboard' => $buttons
  ));
}


function vkApi_usersGet($user_id,$fields_str='') {
  if ($fields_str)
    return _vkApi_call('users.get', array(
      'user_ids' => $user_id,
      'fields' => $fields_str,
      'lang' => 'ru'
    ));
  else
    return _vkApi_call('users.get', array(
      'user_ids' => $user_id,
      'lang' => 'ru'
    ));
}
function vkApi_photosGetMessagesUploadServer($peer_id) {
  return _vkApi_call('photos.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
  ));
}
function vkApi_photosSaveMessagesPhoto($photo, $server, $hash) {
  return _vkApi_call('photos.saveMessagesPhoto', array(
    'photo'  => $photo,
    'server' => $server,
    'hash'   => $hash,
  ));
}
function vkApi_docsGetMessagesUploadServer($peer_id, $type) {
  return _vkApi_call('docs.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
    'type'    => $type,
  ));
}
function vkApi_docsSave($file, $title) {
  return _vkApi_call('docs.save', array(
    'file'  => $file,
    'title' => $title,
  ));
}
function _vkApi_call($method, $params = array()) {
  GLOBAL $token;
  if(!isset($token))
    $token=VK_SERVICE_TOKEN;
  elseif(!$token)
    $token=VK_SERVICE_TOKEN;  
    //echo "token: $token";

  $params['access_token'] = $token;
  $params['v'] = VK_API_VERSION;
  $query = http_build_query($params);
  
  $url = VK_API_ENDPOINT.$method;
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL,$url);
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
  
  //echo '<br><br>'.VK_API_ENDPOINT.$method.'?'.$query;
  
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    //throw new Exception("Failed {$method} request");
    return $error;
  }
  curl_close($curl);
  $response = json_decode($json, true);
  if (!$response || !isset($response['response'])) {
    log_error($json);
    //throw new Exception("Invalid response for {$method} request");
    return $response['error'];
  }
  return $response['response'];
}


function vkApi_upload($url, $file_name) {
  if (!file_exists($file_name)) {
    throw new Exception('File not found: '.$file_name);
  }
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    throw new Exception("Failed {$url} request");
  }
  curl_close($curl);
  $response = json_decode($json, true);
  if (!$response) {
    throw new Exception("Invalid response for {$url} request");
  }
  return $response;
}

?>
