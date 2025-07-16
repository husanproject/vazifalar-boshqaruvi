
<?php
// Sessiyani boshlash va yakunlash
session_start();
session_destroy();
// Foydalanuvchini bosh sahifaga yo‘naltirish
header("Location: index.php");
exit();
?>
