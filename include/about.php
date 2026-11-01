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
          <li><strong>Custom Windows Launcher</strong> &mdash; MikhMon CE branded server launcher, replaces original MikhmonServer.exe</li>
          <li><strong>Cross-platform</strong> &mdash; Runs on any OS with PHP 8.x (Windows/Linux/Mac)</li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-file-text-o"></i> Changelog</h3>
      </div>
      <div class="card-body" style="max-height:400px; overflow-y:auto;">
        <?php
        $changelog = @file('https://raw.githubusercontent.com/kenweill/mikhmon-ce/main/CHANGELOG.md');
        if (!$changelog) $changelog = @file('./CHANGELOG.md');
        if (!$changelog) $changelog = [];
        // escape text, then apply inline **bold** and `code`
        $fmt = function ($t) {
          $t = htmlspecialchars($t);
          $t = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $t);
          return preg_replace('/`(.+?)`/', '<code>$1</code>', $t);
        };
        $in_list = false;  // inside a top-level <ul>
        $li_open = false;  // a top-level <li> is open and may still receive sub-bullets
        $in_sub = false;   // inside a nested <ul>
        // close any open nested list, list item and list
        $close_list = function () use (&$in_list, &$li_open, &$in_sub) {
          if ($in_sub) { echo '</ul>'; $in_sub = false; }
          if ($li_open) { echo '</li>'; $li_open = false; }
          if ($in_list) { echo '</ul>'; $in_list = false; }
        };
        foreach ($changelog as $line) {
          $line = rtrim($line);

          // skip the h1 title line
          if (preg_match('/^# /', $line)) continue;

          // horizontal rule
          if (preg_match('/^---$/', $line)) {
            $close_list();
            echo '<hr>';
            continue;
          }

          // h2 — version headings
          if (preg_match('/^## (.+)$/', $line, $m)) {
            $close_list();
            echo '<h4 style="margin-top:10px;"><strong>' . htmlspecialchars($m[1]) . '</strong></h4>';
            continue;
          }

          // h3 — section headings
          if (preg_match('/^### (.+)$/', $line, $m)) {
            $close_list();
            echo '<h5 style="margin-top:8px;">' . htmlspecialchars($m[1]) . '</h5>';
            continue;
          }

          // blockquote
          if (preg_match('/^> (.+)$/', $line, $m)) {
            $close_list();
            echo '<blockquote style="border-left:3px solid #ccc;padding-left:10px;color:#666;">' . htmlspecialchars($m[1]) . '</blockquote>';
            continue;
          }

          // list item (the <li> stays open so it can hold sub-bullets)
          if (preg_match('/^- (.+)$/', $line, $m)) {
            if ($in_sub) { echo '</ul>'; $in_sub = false; }
            if ($li_open) { echo '</li>'; $li_open = false; }
            if (!$in_list) { echo '<ul>'; $in_list = true; }
            echo '<li style="margin-bottom:4px;">' . $fmt($m[1]);
            $li_open = true;
            continue;
          }

          // nested list item (2 or more leading spaces) under the current list item
          if ($li_open && preg_match('/^\s{2,}- (.+)$/', $line, $m)) {
            if (!$in_sub) { echo '<ul style="margin-top:4px;">'; $in_sub = true; }
            echo '<li style="margin-bottom:2px;">' . $fmt($m[1]) . '</li>';
            continue;
          }

          // blank line
          if ($line === '') {
            $close_list();
            continue;
          }

          // plain paragraph text
          $close_list();
          echo '<p>' . $fmt($line) . '</p>';
        }
        $close_list();
        ?>
      </div>
    </div>
  </div>
</div>
