# MikhMon CE (Community Edition)

> A free, open-source community fork of MikhMon with full compatibility for RouterOS 6 & 7 and PHP 8.x.

---

## Compatibility

| Component | Supported |
|---|---|
| RouterOS 6.x (up to 6.49.x) | ✅ |
| RouterOS 7.x (7.9+, including LTS 7.20.x) | ✅ |
| PHP 8.0 – 8.5+ | ✅ |

---

## Default Login

| Field | Value |
|---|---|
| Username | `admin` |
| Password | `admin` |

> ⚠️ **Change your password immediately** after first login via Admin Settings.

---

## Requirements

- PHP 8.0 or higher
- PHP extensions: `sockets`, `openssl`, `curl`
- MikroTik API service enabled: Winbox → IP → Services → **api** (port 8728)

---

## Installation

📥 Download
1. Download the project as a ZIP from GitHub
2. Extract the ZIP file
3. You will get a folder like MikhmonCE-main
4. Rename the folder to mikhmon-ce

### Windows (Recommended: Laragon)

1. Download and install [Laragon](https://laragon.org/download) (Free, Full version)
2. Start Laragon → Click **Start All**
3. Copy the `mikhmon-ce` folder to `C:\laragon\www\`
4. Open browser → `http://localhost/mikhmon-ce`
5. Login with `admin` / `admin`

### Linux

```bash
sudo apt install apache2 php libapache2-mod-php
sudo cp -r mikhmon-ce /var/www/html/
sudo systemctl restart apache2
# Open http://localhost/mikhmon-ce
```

### macOS

1. Install [MAMP](https://www.mamp.info) or use Homebrew PHP
2. Copy the `mikhmon-ce` folder to your web root
3. Open browser → `http://localhost/mikhmon-ce`

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

- **Date format** — handles both ROS6 (`apr/08/2026`) and ROS7 (`2026-04-08`) automatically
- **Duration format** — handles both ROS6 (`3h15m`) and ROS7 (`03:15:00`, `2d 03:15:00`) automatically
- **Profile on-login script** — updated for ROS7 scheduler `next-run` format changes
- **Background scheduler script** — updated to parse both date formats for expiry checking
- **Uptime/session-time displays** — work correctly on both ROS versions

---

## Known Limitations

### Tamper Protection (Partially Retained)

The original MikhMon includes a tamper detection mechanism via obfuscated JavaScript (notably the `_0x8202` array in `mikhmon.js`). This detects branding changes and can trigger a *"You destroy MIKHMON"* message, breaking core functionality.

Attempts to remove this protection currently cause multiple features to break, indicating tight coupling with core logic. **For this release, tamper protection remains in place** to preserve stability.

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
- Improving modularity and separation of concerns

> **Note for fork maintainers:** Removing tamper-related code may currently break parts of the system. Full rebranding support is a work in progress. Please retain the GPL v2 license and proper credit to the original author.

---

## Credits

- **Original MikhMon** — [Laksamadi Guko](https://github.com/laksa19/mikhmonv3)
- **ROS7 community workaround** — [Vanz J Tutorials](https://www.youtube.com/c/VanzJTutorials)
- **MikhMon CE** — Maintained by the community

---

## License

**GNU General Public License v2** — same as original MikhMon.

This fork respects and acknowledges the original author's work.
