<?php
  include('../config/init.php');
  session_destroy();
  header('Location: ../index.php');
?>
