# Dukan (by Tocaan)

Dukan — A Tocaan Laravel package providing multi-tenant storefront tools.
• Centralized configuration for S3, Cloudflare, and Ploi integrations
• Easy event-driven tenant status logging (TenantStatusLog model & migration)
• Built-in services for AWS S3, Cloudflare cache purging, and Ploi deployments
• Artisan commands to scaffold models, migrations, and more
• Fully publishable config (dukan.php) for environment overrides

## Install

Add to your Laravel project:

```
composer require tocaan/dukan
```
```
php artisan vendor:publish \
  --provider="Tocaan\Dukan\DukanServiceProvider" \
  --tag="config"
```

```
php artisan migrate
```

```
DUKAN_S3_KEY=your-key
DUKAN_S3_SECRET=your-secret
DUKAN_S3_REGION=your-region
```

```
DUKAN_CLOUDFLARE_API_TOKEN=your-cloudflare-token
DUKAN_CLOUDFLARE_ZONE_ID=your-cloudflare-zone
```

```
DUKAN_PLOI_API_TOKEN=your-ploi-token
DUKAN_PLOI_SERVER_ID=your-server-id
```


Access the route:

```
http://yourapp.test/dukan
```
