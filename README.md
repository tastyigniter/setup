# TastyIgniter Setup Wizard

Web installer for [TastyIgniter](https://tastyigniter.com) — designed for shared hosting with **zero shell access**.

Upload this wizard, open `setup.php` in your browser, enter your database credentials, and the installer downloads the pre-vendored TastyIgniter release, writes configuration, and runs `igniter:install` in-process via `Artisan::call()`.

**Full installation guide:** [tastyigniter.com/docs/installation](https://tastyigniter.com/docs/installation)

## Requirements

| Requirement | Minimum |
|-------------|---------|
| PHP | 8.3+ |
| Database | MySQL 8.0+ or MariaDB 10.6+ |
| Web server | Apache or Nginx with URL rewriting |
| Composer / SSH | **Not required** on the host |

Required PHP extensions: `bcmath`, `ctype`, `curl`, `dom`, `exif`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.

## What the wizard does

1. Checks server requirements
2. Downloads the pre-vendored TastyIgniter release from GitHub (includes `vendor/`)
3. Extracts files and writes your `.env` database settings
4. Runs `igniter:install` via Laravel's `Artisan::call()` — no CLI needed
5. Generates a root `.htaccess` redirect to `public/` when needed

## Troubleshooting

See the [installation troubleshooting guide](https://tastyigniter.com/docs/installation#troubleshooting). Detailed logs are written to `setup/setup.log`.

## Development

```bash
cd setup/assets
npm install
npm run dev    # watch mode
npm run build  # production assets → css/app.css, js/app.js
```

## License

MIT — see [LICENSE.txt](LICENSE.txt).
