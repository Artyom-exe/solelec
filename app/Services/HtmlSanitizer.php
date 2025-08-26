<?php

namespace App\Services;

class HtmlSanitizer
{
    protected $purifier;

    public function __construct()
    {
        if (class_exists('\HTMLPurifier')) {
            $config = \HTMLPurifier_Config::createDefault();
            $this->purifier = new \HTMLPurifier($config);
        }
    }

    /**
     * Purify HTML string; fallback to strip_tags preserving line breaks.
     */
    public function sanitize(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        if ($this->purifier) {
            return $this->purifier->purify($html);
        }

        // Fallback: basic stripping but keep new lines
        $clean = strip_tags($html);
        return preg_replace("/(\r?\n){2,}/", "\n\n", $clean);
    }
}
