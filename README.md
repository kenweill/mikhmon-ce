# MikhMon CE (Community Edition)

> A free, open-source community fork of MikhMon with full compatibility for RouterOS 6 & 7 and PHP 8.x.

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

1. Download `MikhMonCE-Windows-v1.2.zip` from the releases page
2. Extract the ZIP to any folder (e.g. `C:\MikhMonCE\`)
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

### Option 2 - Standard (Windows, Linux, macOS with existing web server)

1. Download `MikhMonCE-v1.2.zip` from the releases page
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

---

## Known Limitations

### Tamper Protection (Partially Retained)

The original MikhMon includes a tamper detection mechanism via obfuscated JavaScript (notably the `_0x8202` array in `mikhmon.js`). This detects branding changes and can trigger a "You destroy MIKHMON" message, breaking core functionality.

Attempts to remove this protection currently cause multiple features to break, indicating tight coupling with core logic. For this release, tamper protection remains in place to preserve stability.

### Obfuscated JavaScript (Partially Retained)

Some obfuscated JavaScript remains due to dependencies tied to core features. Deobfuscation is ongoing — see Roadmap below for detailed progress.

---

## Roadmap

### Deobfuscation Progress

The original MikhMon codebase contains obfuscated JavaScript across multiple files. We are gradually replacing all obfuscated code with clean, readable equivalents. Below is the full status:

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
| `settings/vouchereditor.php` | `_0x5b73` | CodeMirror editor init + anti-piracy check | ⏳ Pending |
| `settings/settings.php` | `_0xdf1e` | Session name validator | ⏳ Pending |
| `settings/settings.php` | `_0x1d39` | Domain whitelist restriction | ⏳ Pending |

#### High Priority - Tamper Protection (Risky)
| File | Variable | What It Does | Status |
|---|---|---|---|
| `js/mikhmon.js` | `_0x8202` | Brand tamper check - destroys page if brand changed | ⚠️ Needs careful handling |
| `settings/settings.php` | `_0x8202` | Same brand tamper check embedded in settings page | ⚠️ Needs careful handling |

### Other Roadmap Items

- [ ] Complete deobfuscation of all medium priority files
- [ ] Safely remove tamper protection without breaking functionality
- [ ] Decouple branding logic from core system behavior
- [ ] Achieve fully open-source structure where rebranding is safe
- [ ] RouterOS 7.x real-world testing and verification
- [ ] Version check system pointing to MikhMon CE GitHub releases
- [ ] Auto-update feature for easier upgrades

---

## Contributing

Contributions are welcome! Areas where help is especially needed:

- Testing on RouterOS 7.x devices
- Safely removing tamper protection (`_0x8202`) from `mikhmon.js` and `settings/settings.php`
- Refactoring remaining obfuscated JavaScript (see Roadmap above)

> Note for fork maintainers: Removing tamper-related code may currently break parts of the system.
> Full rebranding support is a work in progress. Please retain the GPL v2 license and proper credit to the original author.

---

## Credits

- **Original MikhMon** - [Laksamadi Guko](https://github.com/laksa19/mikhmonv3)
- **ROS7 community workaround** - [Vanz J Tutorials](https://www.youtube.com/c/VanzJTutorials)
- **MikhMon CE** - Maintained by the community

---

## License

**GNU General Public License v2** - same as original MikhMon.

This fork respects and acknowledges the original author's work.
