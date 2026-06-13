<?php
session_start();
session_destroy();
header("Location: Registration/registration.php");
exit();
?>  