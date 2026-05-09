# 📋 Changelog — MikhMon CE

All notable changes to this project are documented here.

---

## [1.2.0]

### 🚀 New
- New MikhMon CE branded server launcher (`MikhMonCE_Server.exe`) built with AutoIt — open source and community maintainable
- Launcher source code available in `launcher/` folder for transparency and contributions
- Added `launcher/README.md` with compilation instructions

### ✨ Improved
- PHP updates no longer require file renaming — just extract the new PHP zip and go

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

### ⚠️ Known Limitations
- Tamper protection retained for stability — removal planned for a future release
- Some obfuscated JavaScript remains due to core dependencies
- Full deobfuscation is a planned community effort
