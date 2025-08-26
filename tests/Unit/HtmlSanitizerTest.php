<?php

use App\Services\HtmlSanitizer;

it('sanitizes html and strips disallowed tags', function () {
    $sanitizer = new HtmlSanitizer();

    $dirty = '<p>Allowed <script>alert(1)</script><a href="http://example.com">link</a></p>';
    $clean = $sanitizer->sanitize($dirty);

    expect($clean)->not()->toContain('<script>');
    expect($clean)->toContain('Allowed');
    expect($clean)->toContain('link');
});
