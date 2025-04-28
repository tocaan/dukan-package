# Dukan
**Multi-tenant helper services for Laravel** — integrated with [stancl/tenancy](https://tenancyforlaravel.com/) to support SaaS applications. Provides out-of-the-box support for AWS S3, Cloudflare, Ploi, and logs tenant status events.

## Install

Add to your Laravel project:

```
composer require tocaan/dukan
```
```
php artisan vendor:publish --provider="Tocaan\Dukan\DukanServiceProvider"
```

```
php artisan migrate
```
**handle configuration file**
```
DUKAN_S3_KEY=your-key
DUKAN_S3_SECRET=your-secret
DUKAN_S3_REGION=your-region
```

```
DUKAN_CLOUDFLARE_API_TOKEN=your-cloudflare-token
DUKAN_CLOUDFLARE_ZONE_ID=your-cloudflare-zone
DUKAN_CLOUDFLARE_IP=your-cloudflare-ip

```

```
DUKAN_PLOI_API_TOKEN=your-ploi-token
DUKAN_PLOI_SERVER_ID=your-server-id
DUKAN_PLOI_SITE_ID=your-site-id
DUKAN_PLOI_SERVER_IP=your-server-ip
```

[//]: # ()
[//]: # ()
[//]: # (Access the route:)

[//]: # ()
[//]: # (```)

[//]: # (http://yourapp.test/dukan)

[//]: # (```)
