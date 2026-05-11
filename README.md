# MikhMon CE (Community Edition)

> A free, open-source community fork of MikhMon with full compatibility for RouterOS 6 & 7 and PHP 8.x.

🌐 **Website:** [kenweill.github.io/mikhmon-ce](https://kenweill.github.io/mikhmon-ce)

---

## Compatibility

| Component | Supported |
|---|---|
| RouterOS 6.x (up to 6.49.x) | Yes |
| RouterOS 7.x (7.9+, including LTS 7.20.x) | Yes (theoretical - community testing needed) |
| PHP 8.0 - 8.5+ | Yes |

---

## Default Login

| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin` |

> Change your password immediately after first login via Admin Settings.

---

## Requirements

- PHP 8.0 or higher (Windows Bundle includes PHP 8.3.31)
- PHP extensions: `sockets`, `openssl`, `curl`, `mbstring`
- MikroTik API service enabled: Winbox > IP > Services > api (port 8728)

---

## Installation

### Option 1 - Windows Bundle (Recommended for Windows users)

The Windows Bundle includes everything you need - no additional software required.

1. Download the Windows Bundle from the [releases page](https://github.com/kenweill/mikhmon-ce/releases)
2. Extract the ZIP directly to your drive root (e.g. `C:\MikhMonCE-Windows\` or `D:\MikhMonCE-Windows\`)
3. Open the extracted folder
4. Double-click `MikhMonCE_Server.exe` to start the server
5. Server starts automatically and minimizes to system tray
6. Click the tray icon and select **Open MikhMon**
7. Login with `admin` / `admin`

> The Windows Bundle includes PHP 8.3 and the MikhMon CE branded launcher.
> No Laragon, XAMPP, or any other software needed.

> Note: If MikhMonCE_Server.exe fails to start, you may need to install
> Visual C++ Redistributable 2019 x64 from Microsoft:
> https://aka.ms/vs/17/release/vc_redist.x64.exe

> Note: Windows may show a "Windows protected your PC" SmartScreen warning
> when running MikhMonCE_Server.exe for the first time. This is expected for
> unsigned executables. Click **More info** then **Run anyway** to proceed.
> Windows will remember your choice and not ask again.

### Option 2 - Standard (Windows, Linux, macOS with existing web server)

1. Download the Standard version from the [releases page](https://github.com/kenweill/mikhmon-ce/releases)
2. Extract the ZIP file
3. Copy the `mikhmon-ce` folder to your web server root:
   - **Laragon (Windows):** `C:\laragon\www\`
   - **XAMPP (Windows):** `C:\xampp\htdocs\`
   - **Linux:** `/var/www/html/`
   - **macOS (MAMP):** `/Applications/MAMP/htdocs/`
4. Open browser and go to `http://localhost/mikhmon-ce`
5. Login with `admin` / `admin`

### Option 3 - Linux via terminal

```bash
sudo apt install apache2 php libapache2-mod-php php-curl php-mbstring
sudo cp -r mikhmon-ce /var/www/html/
sudo chown -R www-data:www-data /var/www/html/mikhmon-ce
sudo systemctl restart apache2
```

Then open `http://localhost/mikhmon-ce`

### macOS

1. Install [MAMP](https://www.mamp.info) or use Homebrew PHP
2. Copy the `mikhmon-ce` folder to your web root
3. Open browser and go to `http://localhost/mikhmon-ce`

---

## What Was Fixed vs. Original MikhMon

### PHP 8.x Fixes

| Original | Fixed |
|---|---|
| `socket_set_timeout()` | `stream_set_timeout()` |
| `socket_get_status()` | `stream_get_meta_data()` |
| `is_resource()` | Updated for PHP 8 stream objects |
| `implode()` missing separator | Fixed |
| Deprecated string interpolation (`"$arr[$i]"`) | Updated to `"{$arr[$i]}"` |
| `var` class properties | Changed to `public` |

### RouterOS 7.x Fixes

- **Date format** - handles both ROS6 (`apr/08/2026`) and ROS7 (`2026-04-08`) automatically
- **Duration format** - handles both ROS6 (`3h15m`) and ROS7 (`03:15:00`, `2d 03:15:00`) automatically
- **Profile on-login script** - updated for ROS7 scheduler `next-run` format changes
- **Background scheduler script** - updated to parse both date formats for expiry checking
- **Uptime/session-time displays** - work correctly on both ROS versions

### Code Quality

- All obfuscated JavaScript fully removed and replaced with clean, readable code
- Tamper protection completely removed - MikhMon CE is freely rebrandable (GPL v2)
- Domain whitelist restrictions removed - works on any domain or local installation
- Anti-piracy alerts removed - not real security, page access already protected by PHP
- All branding references updated from MIKHMON to MikhMon CE throughout the codebase

---

## Customization

### Rebranding

Since MikhMon CE is fully open source and tamper protection has been completely removed, you are free to rebrand it under the terms of GPL v2.

To change the app name displayed in the sidebar, edit `include/menu.php` and update the two `#brand` elements:

```php
<!-- Desktop sidebar brand name -->
<a id="brand" class="text-center" href="javascript:void(0)">MikhMon CE</a>

<!-- Mobile sidebar brand name -->
<a id="brand" class="text-center" href="./?session=<?= $session; ?>">MikhMon CE</a>
```

Change `MikhMon CE` to your preferred name on both lines. No other files need to be changed for basic rebranding.

> Please retain the GPL v2 license and credit to the original author (Laksamadi Guko) when rebranding or forking.

---

## Roadmap

### Deobfuscation Progress

All obfuscated JavaScript has been fully removed and replaced with clean, readable equivalents.

#### Low Priority - Display/Cosmetic Only
| File | Variable | What It Does | Status |
|---|---|---|---|
| `traffic/trafficmonitor.php` | `_0x381f` | Interface dropdown selector | ✅ Done |
| `traffic/trafficmonitor.php` | `_0xe05e` | Interface session storage reader | ✅ Done |
| `traffic/trafficmonitor.php` | `_0x2f7f` | Highcharts tooltip formatter | ✅ Done |
| `dashboard/home.php` | `_0x2f7f` | Highcharts tooltip formatter | ✅ Done |
| `hotspot/userbyname.php` | `_0x7baa` | Print button click handler | ✅ Done |

#### Medium Priority - Functional but Replaceable
| File | Variable | What It Does | Status |
|---|---|---|---|
| `settings/vouchereditor.php` | `_0x5b73` | CodeMirror editor init + anti-piracy check | ✅ Done |
| `settings/settings.php` | `_0xdf1e` | Session name validator | ✅ Done |
| `settings/settings.php` | `_0x1d39` | Domain whitelist restriction | ✅ Done |

#### High Priority - Tamper Protection
| File | Variable | What It Does | Status |
|---|---|---|---|
| `js/mikhmon.js` | `_0x8202` | Brand tamper check - destroyed page if brand changed | ✅ Done |
| `settings/settings.php` | `_0x8202` | Same brand tamper check on settings page | ✅ Done |

### Other Roadmap Items

| Status | Item |
|---|---|
| ✅ | Complete deobfuscation of all files |
| ✅ | Remove tamper protection safely |
| ✅ | Decouple branding logic from core system behavior |
| ✅ | Achieve fully open-source structure where rebranding is safe |
| ⏳ | RouterOS 7.x real-world testing and verification |
| 🔲 | Version check system pointing to MikhMon CE GitHub releases |
| 🔲 | Auto-update feature for easier upgrades |

---

## Contributing

Contributions are welcome! Areas where help is especially needed:

- Testing on RouterOS 7.x devices
- Reporting bugs or unexpected behavior
- Feature requests and improvements

> Please retain the GPL v2 license and proper credit to the original author when forking.

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for full version history.

---

## Credits

- 🌐 **Website** - [kenweill.github.io/mikhmon-ce](https://kenweill.github.io/mikhmon-ce)
- **Original MikhMon** - [Laksamadi Guko](https://github.com/laksa19/mikhmonv3)
- **ROS7 community workaround** - [Vanz J Tutorials](https://www.youtube.com/c/VanzJTutorials)
- **MikhMon CE** - Maintained by the community

---

## License

**GNU General Public License v2** - same as original MikhMon.

This fork respects and acknowledges the original author's work.
