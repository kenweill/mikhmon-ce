<?php
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
  } else {
        $_SESSION["v"] = "1.3.2 (Based on MikhMon 3.20)";
    }
