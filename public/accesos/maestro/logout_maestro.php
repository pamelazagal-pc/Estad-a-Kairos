<?php
session_start();

unset($_SESSION['maestro_id'], $_SESSION['maestro_nombre'], $_SESSION['maestro_correo']);
session_regenerate_id(true);

header('Location: login_maestro.php');
exit;
