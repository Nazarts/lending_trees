<?php

/* https://api.telegram.org/botXXXXXXXXXXXXXXXXXXXXXXX/getUpdates,
где, XXXXXXXXXXXXXXXXXXXXXXX - токен вашего бота, полученный ранее */
require_once __DIR__.'/gsheets.php';

$token = "1333799071:AAGh16k--um03EA_--rog0tnX1ajFaJCIY0";
$chat_id = "656627630";
// ID таблицы где буду хранится данные
$spreadsheet_id = '14o3EyEM-DRf-Fa5t3B_6eukACsgLULiNHfteif6QAyY';

if(isset($_POST['name'], $_POST['phone'], $_POST['email'])){
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $arr = array(
        'Имя пользователя: ' => $name,
        'Телефон: ' => $phone,
        'Email' => $email
    );
    $txt = '';

    foreach($arr as $key => $value) {
        $txt .= "<b>".$key."</b> ".$value."%0A";
    };

    $sendToTelegram = fopen("https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chat_id}&parse_mode=html&text={$txt}","r");


    $handler = new GSheetsHandler();
    $range = 'A1:E1';
    $handler->writeToSpreadSheet($spreadsheet_id, $range, [$name, $phone, $email]);

    if ($sendToTelegram) {
        header('Location: thankyou.html');
    } else {
        echo "Error";
    }
}
elseif (getopt('client:')){
    GSheetsHandler::getClient();
}
?>