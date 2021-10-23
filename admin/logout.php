<?php 

session_start();

unset($_SESSION['Admin']);

// session_destroy();

header('Location: http://localhost/BLOGX/admin/login.php');
exit();

?>