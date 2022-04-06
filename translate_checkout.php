<?php

//CheckOut.php v0.1 for Smart Sender


//------------------

ini_set('max_execution_time', '1700');
set_time_limit(1700);


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

//------------------

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); //convert JSON into array

//------------------

$userId = $input["userId"];
$text = $input["text"];
$ss_token = $input["token"];
$null = $input["null"];

// functions
{
function send_forward($inputJSON, $link){
	
$request = 'POST';	
		
$descriptor = curl_init($link);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, $inputJSON);
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $request);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
function send_bearer($url, $token, $type = "GET", $param = []){
	
		
$descriptor = curl_init($url);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer '.$token)); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
}

// Проверка входящих данных
if ($ss_token == NULL || $userId == NULL) {
    $result["status"] = "error";
    if ($ss_token == NULL) {
        $result["message"][] = "Вы не указали токен SmartSender. Он нужен для получения информации.";
    }
    if ($userId == NULL) {
        $result["message"][] = "Вы не указали идентификатор пользователя. Система не знает, чью информацию нужно использовать.";
    }
    echo json_encode($result);
    $log["result"] = $result;
    send_forward(json_encode($log), $url);
    exit;
}

// Получение данных из корзины
$cursor = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId."/checkout?page=1&limitation=20", $ss_token), true);
if ($cursor["error"] != NULL && $cursor["error"] != 'undefined') {
    $result["status"] = "error";
    $result["message"][] = "Ошибка получения данных из SmartSender";
    if ($cursor["error"]["code"] == 404 || $cursor["error"]["code"] == 400) {
        $result["message"][] = "Пользователь не найден. Проверте правильность идентификатора пользователя и приналежность токена к текущему проекту.";
    } else if ($cursor["error"]["code"] == 403) {
        $result["message"][] = "Токен проекта SmartSender указан неправильно. Проверте правильность токена.";
    }
    echo json_encode($result);
    exit;
}
$pages = $cursor["cursor"]["pages"];
$count = 1;
for ($i = 1; $i <= $pages; $i++) {
    $checkout = json_decode (send_bearer("https://api.smartsender.com/v1/contacts/".$userId."/checkout?page=".$i."&limitation=20", $ss_token), true);
    $log["checkout"][] = $checkout;
	$essences = $checkout["collection"];
	$currency = $essences[0]["cash"]["currency"];
	foreach ($essences as $product) {
		$message = $message.$count." 🔸 ".$product["pivot"]["quantity"]." x ".$product["product"]["name"]." ".$product["name"]." — ".$product["pivot"]["quantity"] * $product["cash"]["amount"]."  ".$product["cash"]["currency"]."\n";
    	$summ[] = $product["pivot"]["quantity"]*$product["cash"]["amount"];
    	$count ++;
    }
}

if ($message != NULL) {
    $search[] = "{checkout}";
    $replace[] = $message;
    $search[] = "{sum}";
    $replace[] = array_sum($summ);
    $search[] = "{currency}";
    $replace[] = $currency;
} else {
    $search = $text;
    $replace = $null;
}



$send["type"] = "text";
$send["watermark"] = 1;
$send["content"] = str_replace($search, $replace, $text);
$result["Smart Sender"] = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId."/send", $ss_token, "POST", $send), true);

echo json_encode($result);
