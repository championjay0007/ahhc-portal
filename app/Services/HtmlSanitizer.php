<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Sanitize HTML using HTMLPurifier when available, otherwise a conservative fallback.
     */
    public static function sanitize(string $html): string
    {
        // Use HTMLPurifier if installed
        if (class_exists('\\HTMLPurifier')) {
            $config = \HTMLPurifier_Config::createDefault();
            // allow some safe attributes
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com/embed/|player.vimeo.com/video/)%');
            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($html);
        }

        // Conservative fallback: allow a small set of tags and remove event handlers and javascript: URIs
        $allowedTags = '<p><br><b><strong><i><em><ul><ol><li><a><blockquote><pre><code><img>';
        $clean = strip_tags($html, $allowedTags);

        // remove on* attributes
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean);

        // remove javascript: in href/src
        $clean = preg_replace_callback('/<(a|img)\b[^>]*>/i', function ($m) {
            $tag = $m[0];
            // remove javascript: from href/src
            $tag = preg_replace('/(href|src)\s*=\s*("|\')?\s*javascript:[^"\'\s>]*("|\')?/i', '$1="#"', $tag);
            return $tag;
        }, $clean);

        return $clean;
    }
}
