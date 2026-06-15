# Cappuccino

[中文文档](README.zh-CN.md)

A lightweight WordPress plugin that appends a donation image (e.g. WeChat or Alipay QR code) to the bottom of your posts.

## Features

- Upload a donation image from the WordPress Media Library
- Customizable hint text with multi-line support
- Toggle hint text visibility independently
- Optional click-through link on the image
- Choose which public post types display the donation block
- Left, center, or right alignment
- Adjustable maximum image width

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Installation

1. Download or clone this repository.
2. Copy the `cappuccino` folder into `wp-content/plugins/`.
3. Activate **Cappuccino** under **Plugins** in the WordPress admin.
4. Go to **Settings → Cappuccino** to upload your image and configure options.

## Configuration

| Setting | Description |
| --- | --- |
| Enable donation | Show or hide the donation block on supported post types |
| Donation image | QR code or payment image from the Media Library |
| Hint text | Optional message above the image; each line renders as a separate paragraph |
| Click link | Optional URL opened when the image is clicked |
| Post types | Public post types where the block should appear |
| Alignment | Left, center, or right |
| Max width | Image width in pixels (50–800) |

## FAQ

**Does the image appear on archive or list pages?**

No. The block is shown only on singular post views.

**Can hint text span multiple lines?**

Yes. Each line in the hint text field is rendered as its own paragraph.

**Will my settings be preserved after upgrading from wp-donate?**

Yes. Cappuccino migrates settings from legacy option keys (`wpd_settings`, `wpad_settings`) on first load.

## Project structure

```
cappuccino/
├── cappuccino.php          # Plugin bootstrap
├── includes/
│   ├── class-admin.php     # Settings page
│   └── class-frontend.php  # Frontend output
├── assets/
│   ├── css/
│   └── js/
└── readme.txt              # WordPress.org-style readme
```

## License

This project is licensed under the **GNU General Public License v2.0 or later**.

See the [LICENSE](LICENSE) file for the full license text.
