<?php
session_start();
unset($_SESSION['user']);
session_regenerate_id(true);
header("Location: /shoe-shop/index.php");
exit;
