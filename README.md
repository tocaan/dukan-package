# Dukan (by Tocaan)

Simple Laravel package to demonstrate structure, config publishing, routes, and views.

## Install

Add to your Laravel project:

```
composer require tocaan/dukan
```

If using locally via path repo, link it in `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "../dukan"
  }
]
```

Then:

```bash
php artisan vendor:publish --tag=config
```

Access the route:

```
http://yourapp.test/dukan
```
