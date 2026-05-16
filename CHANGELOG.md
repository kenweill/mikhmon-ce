# 📋 Changelog — MikhMon CE
All notable changes to this project are documented here.

---

## [Unreleased]

### 🔧 Fixed
- **`hotspot/users.php`** — Initialized `$acomment` to an empty string before the concatenation loop; previously undefined, causing a leading empty entry in the comment filter dropdown
- **`hotspot/users.php`** — Moved `</tr>` inside the user table loop so every row is properly closed; it was placed outside the loop, leaving all rows except the last unclosed
- **`include/readcfg.php`** — Removed dead `$sesname` variable that read from `$data[$session][10]` using the wrong delimiter (`+`); the field uses `=` for idle timeout, making `$sesname` always empty and never used
- **`hotspot/userbyname.php`** — Added missing `name="mac"` attribute to the Mac Address input field and wired `mac-address` into the RouterOS API set call; previously the MAC was displayed but never saved on form submit
- **`process/removehotspotuserbycomment.php`** — Added script and scheduler cleanup before user removal to match the behaviour of `removehotspotuser.php`; orphaned RouterOS scripts and schedulers were left behind on every bulk delete-by-comment. Added null/empty guards so users with no linked script or scheduler (e.g. manually created users) are safely skipped without sending a bad API call
- **`process/removeexpiredhotspotuser.php`** — Same orphan cleanup fix applied to the remove-expired-users bulk action, with the same null/empty guards
- **`hotspot/generateuser.php`** — Fixed `=` (assignment) used instead of `==` (comparison) in the Close button `elseif` condition; the branch always evaluated to true, so Close always navigated to `users-by-profile` regardless of where the user came from
- **`hotspot/generateuser.php`** — Removed duplicate `<option>4</option>` from the user length dropdown; `4` appeared twice, making the list start at 4 twice and skipping the correct order from 3 upward

---

## [1.3.0]
> Full deobfuscation release — MikhMon CE is now completely open source with no hidden code.

### 🔧 Fixed — Deobfuscation Complete
- Removed all obfuscated JavaScript (`_0x` variables) across the entire codebase
- All code is now clean, readable, and fully open source compliant

### 🗑️ Removed
- **Brand tamper check** (`_0x8202`) from `js/mikhmon.js` — original code destroyed the page if brand name was changed. Removed as MikhMon CE is GPL v2 licensed and rebranding is permitted
- **Brand tamper check** (`_0x8202`) from `settings/settings.php` — same check on settings page load. Removed for same reason
- **Domain whitelist restriction** (`_0x1d39`) from `settings/settings.php` — original code only allowed ping test on `xban.xyz`, `logam.id`, `minis.id`. Removed so ping test works on all domains
- **Anti-piracy alert** from `settings/vouchereditor.php` — `"Mikhmon bajakan! :)"` alert on voucher editor. Removed as it was not real security — PHP session check already handles authentication
- **Original MikhMon version check** from `settings/sessions.php` — removed check that fetched version from original MikhMon GitHub repo

### ✨ Improved
- **Ping test** now works on all domains and local installations
- **Session name validator** (`_0xdf1e`) deobfuscated and updated — reserved names now include `mikhmon-ce` variants
- **Highcharts tooltip** (`_0x2f7f`) in `traffic/trafficmonitor.php` and `dashboard/home.php` deobfuscated — tooltip now shows `MikhMon CE Traffic Monitor`
- **Interface selector** (`_0x381f`, `_0xe05e`) in `traffic/trafficmonitor.php` deobfuscated
- **Print button handler** (`_0x7baa`) in `hotspot/userbyname.php` deobfuscated
- **CodeMirror editor init** (`_0x5b73`) in `settings/vouchereditor.php` deobfuscated
- **idleTimer()** in `js/mikhmon.js` rewritten cleanly — session timeout functionality fully preserved

### 🏷️ Branding
- All `MIKHMON` references in codebase updated to `MikhMon CE`
- `Mikhmon Traffic Monitor` tooltip updated to `MikhMon CE Traffic Monitor`

---

## [1.2.0]

### 🚀 New
- New MikhMon CE branded server launcher (`MikhMonCE_Server.exe`) built with AutoIt — open source and community maintainable
- Launcher source code available in `launcher/` folder for transparency and contributions
- Added `launcher/README.md` with compilation instructions

### ✨ Improved
- PHP updates no longer require file renaming — just extract the new PHP zip and go
- Server auto-starts on launch and minimizes to system tray when closed
- Tray icon with right-click menu: Show, Open MikhMon, Stop Server, Exit

### 🔄 Changed
- Replaced original `MikhmonServer.exe` with new branded `MikhMonCE_Server.exe`
- Windows Bundle now uses standard `php.exe` instead of renamed `m-php.exe`

---

## [1.1.0]

### 🚀 New
- Windows Bundle (`MikhMonCE-Windows-v1.1.zip`) — includes everything needed for Windows users
- Bundled PHP 8.3 built-in server — no Laragon, XAMPP, or any other software needed
- Pre-configured `php.ini` with all required extensions enabled for MikhMon CE

### 📝 Notes
- No changes to core MikhMon CE functionality from v1.0.0
- Users already running v1.0.0 on Laragon or another web server do not need to update
- Windows Bundle is an additional installation option only

---

## [1.0.0]
> First stable public release of MikhMon CE — a community fork of MikhMon by [Laksamadi Guko](https://github.com/laksa19/mikhmonv3).

### 🔧 Fixed — PHP 8.x Compatibility
- `socket_set_timeout()` replaced with `stream_set_timeout()`
- `socket_get_status()` replaced with `stream_get_meta_data()`
- `is_resource()` updated for PHP 8 stream objects
- `implode()` missing separator argument fixed
- Deprecated string interpolation fixed across multiple files (`"$arr[$i]"` → `"{$arr[$i]}"`)
- `var` class properties changed to `public`

### ✨ Improved — RouterOS 7.x Compatibility
- Added automatic ROS6/ROS7 date format detection (`apr/08/2026` vs `2026-04-08`)
- Added automatic ROS6/ROS7 duration format detection (`3h15m` vs `03:15:00`)
- Updated profile on-login scripts for ROS7 scheduler compatibility
- Updated background scheduler to parse both ROS6 and ROS7 date formats
- All uptime and session-time displays work correctly on both ROS versions

### 🏷️ Branding
- Rebranded to MikhMon CE (Community Edition)
- Updated About page with credits and changelog
- Default login: `admin` / `admin` — change immediately after first login
