<?php
// BC210143 Dolly
session_start();
session_destroy();
header("Location: index.php");
exit();
?>