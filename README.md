# maispace/mai-account — TYPO3 Extension
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://www.php.net/)
[![TYPO3](https://img.shields.io/badge/TYPO3-13.4%20LTS-orange)](https://typo3.org/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)

Frontend user extension providing login, registration, MFA, and full profile management including interests and reminders. Extends the TYPO3 core `felogin` plugin with MFA, registration, and profile features. Newsletter opt-in is a UI entry point only — subscriber records are owned by `mai_newsletter`.

**Requires:** TYPO3 13.4 LTS / 14.0 · PHP 8.2+

---

## Installation

```bash
composer require maispace/mai-account
```

---

## Development

### Linting

```bash
composer lint:check     # Run all linters
composer lint:fix       # Fix auto-fixable issues
```

### Testing

```bash
composer test           # Run all tests
composer test:unit      # Run unit tests only
```

---

## License

GPL-2.0-or-later — see [LICENSE](../../LICENSE) for details.
