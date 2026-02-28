# 🍪 CookiEzu — Cookie Consent Plugin for WordPress

**Lightweight, GDPR-compliant cookie consent management. Open source & free forever.**

---

## Features

- 🎨 **3 layouts** — Bar, Box, Modal
- 📍 **4 positions** — Bottom, Top, Bottom-Left, Bottom-Right
- 🌗 **3 themes** — Light, Dark, Custom (full colour control)
- ✅ **4 cookie categories** — Necessary, Analytics, Marketing, Functional
- 📋 **Cookie details table** in the preference panel
- 🔒 **Consent log** with GDPR audit trail (database)
- 🔗 **Google Analytics 4** auto-loader + Consent Mode v2
- 🏷️ **Google Tag Manager** dataLayer integration
- 🌍 **Translation-ready** (.pot included)
- 🧩 **Custom CSS** field for overrides
- ⚡ **Zero dependencies** — no jQuery on the front end

---

## Installation

1. Upload the `cookiezu` folder to `/wp-content/plugins/`.
2. Activate via **Plugins → Installed Plugins**.
3. Go to **CookiEzu → Settings** and configure the banner.

---

## File Structure

```
cookiezu/
├── cookiezu.php                  # Plugin entry point
├── README.md
├── LICENSE
├── admin/
│   ├── css/cookiezu-admin.css
│   ├── js/cookiezu-admin.js
│   └── views/
│       ├── settings-page.php
│       └── log-page.php
├── public/
│   ├── css/cookiezu-public.css
│   ├── js/cookiezu-public.js
│   └── views/banner.php
├── includes/
│   ├── class-cookiezu.php
│   ├── class-cookiezu-installer.php
│   └── class-cookiezu-settings.php
└── languages/
```

---

## JavaScript API

Listen for consent updates anywhere in your theme or plugins:

```js
document.addEventListener('cookiezuConsentUpdated', function (e) {
  var consent = e.detail;
  // consent.necessary  → true
  // consent.analytics  → true/false
  // consent.marketing  → true/false
  // consent.functional → true/false

  if (consent.analytics) {
    // load analytics scripts
  }
});
```

---

## Hooks & Filters

| Hook | Type | Description |
|---|---|---|
| `cookiezu_options` | Filter | Modify options array before use |
| `cookiezu_banner_html` | Filter | Override the full banner HTML |

---

## Contributing

Pull requests are welcome! Please open an issue first to discuss what you'd like to change.

1. Fork the repo
2. Create your branch (`git checkout -b feature/my-feature`)
3. Commit your changes
4. Push and open a Pull Request

---

## License

[GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html)
