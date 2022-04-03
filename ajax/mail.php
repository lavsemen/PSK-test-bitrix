<?
header('Content-type: aplication/json');

$to = 'lavsemen2108@gmail.com'; // почта для отправки
$subject = 'Заявка на квартиру № '.$_POST['id'].'  от ' . $_POST['name'];
$message = 'Поступила заявка от ' . $_POST['name'] .  '. Номер телефона: ' . $_POST['phone'];
$headers = 'From: noreplay@mail.ru'       . "\r\n" .
           'Reply-To: noreplay@mail.ru' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $message, $headers)) {
  echo json_encode([
    'status' => 200,
    'text' => 'Ваше сообщение успешно отправленно!',
  ]);
} else { 
  echo json_encode([
    'status' => 400,
    'text' => 'Ошибка отправки',
  ]);
}

