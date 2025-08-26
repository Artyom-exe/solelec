<?php

return [
    // HTMLPurifier configuration array passed to HTMLPurifier_Config::createDefault()
    // See: http://htmlpurifier.org/live/configdoc/plain.html
    'settings' => [
        'HTML.SafeIframe' => true,
        'URI.SafeIframeRegexp' => '%^https?://(www.youtube.com/embed/|player.vimeo.com/video/)%',
        'HTML.Allowed' => 'p,br,strong,em,ul,ol,li,a[href],img[src|alt|title],h1,h2,h3,h4,h5,h6',
        'Attr.AllowedFrameTargets' => ['_blank'],
    ],
    // Enable caching directory (ensure writable in production)
    'cache' => [
        'enabled' => true,
        'path' => storage_path('app/htmlpurifier'),
    ],
];
