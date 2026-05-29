<?php
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
  } else {
        $_SESSION["v"] = "1.3.4 (Based on MikhMon 3.20)";
    }
