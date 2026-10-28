<?php
$plain_password = 'admin123'; // password admin yang kamu mau
$hash = password_hash($plain_password, PASSWORD_DEFAULT);
echo $hash;
?>