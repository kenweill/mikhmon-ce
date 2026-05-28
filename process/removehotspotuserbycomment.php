<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
$getuser = $API->comm("/ip/hotspot/user/print", array(
  "?comment" => "$removehotspotuserbycomment",
  "?uptime" => "00:00:00"
));
$TotalReg = count($getuser);

$_SESSION['ubp'] = $getuser[0]['profile'];
$_SESSION['ubc'] = "";

// Collect all user names for batch script/scheduler lookup
$names = array();
$uids = array();
for ($i = 0; $i < $TotalReg; $i++) {
  $uids[] = $getuser[$i]['.id'];
  $names[] = $getuser[$i]['name'];
}

// Fetch all scripts and schedulers in one call each
$allscripts = $API->comm("/system/script/print");
$allschedulers = $API->comm("/system/scheduler/print");

// Match by name and collect IDs to remove
$scrids = array();
$schids = array();
foreach ($allscripts as $scr) {
  if (in_array($scr['name'], $names) && !empty($scr['.id'])) {
    $scrids[] = $scr['.id'];
  }
}
foreach ($allschedulers as $sch) {
  if (in_array($sch['name'], $names) && !empty($sch['.id'])) {
    $schids[] = $sch['.id'];
  }
}

// Batch remove scripts, schedulers, and users
if (!empty($scrids)) {
  $API->comm("/system/script/remove", array(".id" => implode(",", $scrids)));
}
if (!empty($schids)) {
  $API->comm("/system/scheduler/remove", array(".id" => implode(",", $schids)));
}
if (!empty($uids)) {
  $API->comm("/ip/hotspot/user/remove", array(".id" => implode(",", $uids)));
}
if ($_SESSION['ubp'] != "") {
  echo "<script>window.location='./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session . "'</script>";
} else {
  echo "<script>window.location='./?hotspot=users&profile=all&session=" . $session . "'</script>";
}

?>