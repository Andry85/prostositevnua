<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Создание формы обратной связи</title>
<meta http-equiv="Refresh" content="4; URL=http://prostosait.com.ua/"> 
<style type="text/css">
	img{width:auto\9;height:auto;max-width:100%;vertical-align:middle;border:0;-ms-interpolation-mode:bicubic;}
</style>
</head>
<body>

<?php 

$sendto   = "prostosite.vn.ua@gmail.com"; // почта, на которую будет приходить письмо
$username = $_POST['name'];   // сохраняем в переменную данные полученные из поля c именем
$usertel = $_POST['telephone']; // сохраняем в переменную данные полученные из поля c телефонным номером
$usermail = $_POST['email']; // сохраняем в переменную данные полученные из поля c адресом электронной почты
$idea = $_POST['idea'];
$likes = $_POST['likes'];
$dontlikes = $_POST['dontlikes'];
$typeofsite = $_POST['typeofsite'];     
$sectionofsite = $_POST['sectionofsite'];
$pages = $_POST['pages'];
$languiges = $_POST['languiges'];
$styleofsite = $_POST['styleofsite'];




// Формирование заголовка письма
$subject  = "A new message";
$headers  = "From: " . strip_tags($usermail) . "\r\n";
$headers .= "Reply-To: ". strip_tags($usermail) . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html;charset=utf-8 \r\n";

// Формирование тела письма
$msg  = "<html><body style='font-family:Arial,sans-serif;'>";
$msg .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>Cообщение с сайта prostosait.com.ua</h2>\r\n";
$msg .= "<p><strong>От кого:</strong> ".$username."</p>\r\n";
$msg .= "<p><strong>Почта:</strong> ".$usermail."</p>\r\n";
$msg .= "<p><strong>Телефон:</strong> ".$usertel."</p>\r\n";
$msg .= "<p><strong>Суть проекта:</strong> ".$idea."</p>\r\n";



$msg .= "<p><strong>Сайты, которые нравятся:</strong> ".$likes."</p>\r\n";
$msg .= "<p><strong>Сайты, которые не нравятся:</strong> ".$dontlikes."</p>\r\n";
$msg .= "<p><strong>Тип сайта:</strong> ".$typeofsite."</p>\r\n";



if ($sectionofsite){
    foreach ($sectionofsite as $sec)
    	{

    		$msg .= "<p><strong>Основные разделы сайта:</strong> ".$sec."</p>\r\n";
		}
 }



$msg .= "<p><strong>Примерное количество страниц сайта:</strong> ".$pages."</p>\r\n";

$msg .= "<p><strong>Стиль сайта:</strong> ".$styleofsite."</p>\r\n";


if ($languiges){
    foreach ($languiges as $lan)
    	{

    		$msg .= "<p><strong>Языки сайта:</strong> ".$lan."</p>\r\n";
		}
}




$msg .= "</body></html>";

// отправка сообщения
if(@mail($sendto, $subject, $msg, $headers)) {
	echo "<center><img src='http://prostosait.com.ua/theme/general/images/spasibo.png'></center>";
} else {
	echo "<center><img src='http://prostosait.com.ua/theme/general/images/ne-otpravleno.png'></center>";
}

?>

</body>
</html>