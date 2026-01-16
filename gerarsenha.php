<?php
$password = '123mudar'; // Substitua pela senha desejada
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>