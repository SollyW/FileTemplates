# File Templates
Allows uploaded files to be inlined into MediaWiki pages

## Installation
- Extract and place FileTemplates into your MediaWiki extensions directory
- Add `wfLoadExtension( 'FileTemplates' );` to LocalSettings.php

## Configuration
Configure allowed MIME types:

`LocalSettings.php`
```php
$wgFileTemplatesAllowedMimeTypes = [
        'image/svg+xml',
        '...',
        '...',
];
```
**Files matching this list can be used by anyone.** Make sure files uploaded to your wiki are fully sanitised to prevent XSS and other vulnerabilities
