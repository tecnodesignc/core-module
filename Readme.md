#Core Modules

To activate the Cache System

Modify

```php
// config/app.php

'providers' => [
    ...
    Modules\Core\Pagecache\ResponseCache\ResponseCacheServiceProvider::class,
];
```

This package also comes with a facade.

```php
// config/app.php

'aliases' => [
    ...
   'ResponseCache' => Modules\Core\Pagecache\ResponseCache::class,
];
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Modules\Core\Pagecache\ResponseCache\ResponseCacheServiceProvider"
```

Command available to clear cache

```bash
php artisan pagecache:clear
```

### URL rewriting

In order to serve the static files directly once they've been cached, you need to properly configure your web server to check for those static files.

- **For nginx:**

  Update your `location` block's `try_files` directive to include a check in the `page-cache` directory:

    ```nginxconf
    location / {
        try_files $uri $uri/ /page-cache/$uri.html /index.php?$query_string;
    }
    ```

- **For apache:**

  Open `public/.htaccess` and add the following before the block labeled `Handle Front Controller`:

    ```apacheconf
    # Serve Cached Page If Available...
    RewriteCond %{REQUEST_URI} ^/?$
    RewriteCond %{DOCUMENT_ROOT}/page-cache/pc__index__pc.html -f
    RewriteRule .? page-cache/pc__index__pc.html [L]
    RewriteCond %{DOCUMENT_ROOT}/page-cache%{REQUEST_URI}%{QUERY_STRING}.html -f
    RewriteRule . page-cache%{REQUEST_URI}.html [L]
    ```
