<?php
/*
 *  Original MikhMon - Copyright (C) 2018 Laksamadi Guko.
 *  MikhMon CE (Community Edition) - Community Fork
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 */
session_start();
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
}
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> About</h3>
      </div>
      <div class="card-body">
        <h3>MikhMon CE v<?= $_SESSION['v']; ?></h3>
        <p>
          MikhMon CE (Community Edition) is a free, open-source community fork of MikhMon,
          updated for compatibility with RouterOS 6 &amp; 7 and PHP 8.x.
        </p>
        <p>
        <ul>
          <li>Fork Maintainer : Community</li>
          <li>Original Author : <a href="https://github.com/laksa19" target="_blank">Laksamadi Guko</a></li>
          <li>Licence : <a href="https://github.com/laksa19/mikhmonv3/blob/master/LICENSE" target="_blank">GPLv2</a></li>
          <li>Original MikhMon : <a href="https://github.com/laksa19/mikhmonv3" target="_blank">github.com/laksa19/mikhmonv3</a></li>
          <li>ROS7 community workaround : <a href="https://www.youtube.com/c/VanzJTutorials" target="_blank">Vanz J Tutorials</a></li>
          <li>MikhMon CE GitHub : <a href="https://github.com/kenweill/mikhmon-ce" target="_blank">github.com/kenweill/mikhmon-ce</a></li>
          <li>API Class : <a href="https://github.com/BenMenking/routeros-api" target="_blank">routeros-api</a></li>
        </ul>
        </p>
        <p>
          Compatible with RouterOS 6.x and RouterOS 7.x (7.9 and above).<br>
          Requires PHP 8.0 or higher.
        </p>
        <div>
          <i>Original MikhMon &copy; 2018 Laksamadi Guko &mdash; MikhMon CE &copy; <?= date('Y'); ?> Community</i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-list-alt"></i> What's New in MikhMon CE</h3>
      </div>
      <div class="card-body">
        <ul>
          <li><strong>PHP 8.x compatibility</strong> &mdash; Fixed all deprecated and removed functions</li>
          <li><strong>RouterOS 7.x support</strong> &mdash; Date/time format changes handled automatically</li>
          <li><strong>RouterOS 6.x backward compatible</strong> &mdash; Works with both ROS6 and ROS7</li>
          <li><strong>Improved profile scripts</strong> &mdash; On-login and scheduler scripts updated for ROS7</li>
          <li><strong>Windows Bundle available</strong> &mdash; Includes built-in server, no Laragon or XAMPP needed for Windows users</li>
          <li><strong>Cross-platform</strong> &mdash; Runs on any OS with PHP 8.x (Windows/Linux/Mac)</li>
        </ul>
      </div>
    </div>
  </div>
</div>
