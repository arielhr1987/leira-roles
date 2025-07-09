# Roles & Capabilities

Easily manage your site's user roles and capabilities with an intuitive interface — directly from the WordPress admin.

[![WordPress Version](https://img.shields.io/badge/WordPress-4.1%2B-blue.svg)](https://wordpress.org)  
[![Tested Up To](https://img.shields.io/badge/Tested%20Up%20To-6.6-green.svg)](https://wordpress.org/plugins/)  
[![Requires PHP](https://img.shields.io/badge/PHP-7.4%2B-8892bf.svg)](https://www.php.net/)  
[![GPLv2 or later](https://img.shields.io/badge/license-GPLv2--or--later-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)

---

## 💡 Features

- Create, edit, and delete **custom roles**
- Assign and revoke **capabilities** to roles or individual users
- Clone roles for quick duplication
- Define new **custom capabilities**
- Clean, secure interface with role-based access control
- No code required — full control from the WordPress admin
- Built following [WordPress coding standards](https://developer.wordpress.org/coding-standards/)

> 🔐 Access is restricted to site administrators. No additional capability will grant access to these tools.

---

## 📷 Screenshots

| ![Roles page](.wordpress-org/screenshot-4.png) | ![Capabilities page](.wordpress-org/screenshot-8.png) |
|--|--|
| Manage and clone roles | Manage capabilities with protection for system items |

More screenshots are available in the [WordPress plugin page](https://wordpress.org/plugins/leira-roles/).

---

## 🚀 Installation

1. Upload the `leira-roles` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Users → Roles** or **Users → Capabilities** in the admin sidebar.

---

## 📚 FAQ

### Can I create new user roles?
Yes. You can create any number of custom roles and assign the capabilities that suit your needs.

### Can I delete WordPress default roles?
No. Core roles are protected and cannot be removed.

### Can I assign or remove capabilities from a specific user?
Yes. You can fine-tune capabilities for individual users from the Users list.

### Can I create custom capabilities?
Yes. You can define and assign new custom capabilities as needed.

---

## 🧪 Development

This plugin is open source. View or contribute to the source code on [GitHub](https://github.com/arielhr1987/leira-roles).

Development is powered by:
* [`@wordpress/env`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/) — for running a local WordPress environment via Docker.
* [`@wordpress/scripts`](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/) — for asset compilation and build tools.

To get started:

1. Clone the repository and run `npm install`.
2. Start the environment with `wp-env start`.
3. The plugin will be automatically mounted and activated in the local site:
  - Site URL: `http://localhost:8888`
  - Admin: `http://localhost:8888/wp-admin` (user: `admin`, password: `password`)
4. Use `wp-env stop` to shut down the environment.

To build assets for production:

```bash
npm run build

🛠 Local development setup:
```bash
npm install
npm run start
