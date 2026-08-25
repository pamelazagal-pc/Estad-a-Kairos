<?php
session_start();
unset($_SESSION['tutor_id'], $_SESSION['tutor_nombre']);
session_regenerate_id(true);
header('Location: login_tutor.php');
exit;
