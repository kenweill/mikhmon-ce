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

- PHP 8.0 or higher
- PHP extensions: `sockets`, `openssl`, `curl`, `mbstring`
- MikroTik API service enabled: Winbox > IP > Services > api (port 8728)

---

## Installation

### Option 1 - Windows Bundle (Recommended for Windows users)

The Windows Bundle includes everything you need - no additional software required.

1. Download `MikhMonCE-Windows-v1.1.zip` from the releases page
2. Extract the ZIP to any folder (e.g. `C:\MikhMonCE\`)
3. Open the extracted folder
4. Double-click `MikhmonServer.exe` to start the server
5. Click **Start Server**
6. Click **Open Mikhmon**
7. Login with `admin` / `admin`

> The Windows Bundle includes PHP 8.3 and the MikhmonServer launcher.
> No Laragon, XAMPP, or any other software needed.

> Note: If MikhmonServer.exe fails to start, you may need to install
> Visual C++ Redistributable 2019 x64 from Microsoft:
> https://aka.ms/vs/17/release/vc_redist.x64.exe

### Option 2 - Standard (Windows, Linux, macOS with existing web server)

1. Download `MikhMonCE-v1.1.zip` from the releases page
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

Some obfuscated JavaScript remains due to dependencies tied to core features. Partial cleanup has been explored, but full removal causes unintended side effects. The goal is to gradually refactor these sections in future versions.

---

## Roadmap

- [ ] Remove all tamper protection mechanisms without breaking functionality
- [ ] Fully deobfuscate JavaScript codebase
- [ ] Decouple branding logic from core system behavior
- [ ] Achieve a true fully open-source structure where rebranding and modification are safe

---

## Contributing

Contributions are welcome! Areas where help is especially needed:

- Reverse-engineering obfuscated logic
- Refactoring core JavaScript components
- Testing on RouterOS 7.x devices

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
