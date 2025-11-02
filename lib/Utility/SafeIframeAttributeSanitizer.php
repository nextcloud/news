<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @copyright 2025 Nextcloud GmbH and Nextcloud contributors
 */

namespace OCA\News\Utility;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Custom attribute sanitizer for iframe src attributes
 *
 * Only allows iframes from trusted video providers: YouTube, Vimeo, and VK
 */
class SafeIframeAttributeSanitizer implements AttributeSanitizerInterface
{
    private const ALLOWED_IFRAME_HOSTS = [
        'www.youtube.com',
        'youtube.com',
        'www.youtube-nocookie.com',
        'youtube-nocookie.com',
        'player.vimeo.com',
        'vimeo.com',
        'vk.com',
    ];

    private const ALLOWED_IFRAME_PATH_PREFIXES = [
        'youtube.com' => ['/embed/'],
        'www.youtube.com' => ['/embed/'],
        'youtube-nocookie.com' => ['/embed/'],
        'www.youtube-nocookie.com' => ['/embed/'],
        'player.vimeo.com' => ['/video/'],
        'vimeo.com' => ['/video/'],
        'vk.com' => ['/video_ext.php'],
    ];

    public function getSupportedElements(): ?array
    {
        return ['iframe'];
    }

    public function getSupportedAttributes(): ?array
    {
        return ['src'];
    }

    public function sanitizeAttribute(
        string $element,
        string $attribute,
        string $value,
        HtmlSanitizerConfig $config
    ): ?string {
        // Only process iframe src attributes
        if ($element !== 'iframe' || $attribute !== 'src') {
            return $value;
        }

        // Parse the URL
        $parsedUrl = parse_url($value);
        if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            // Invalid URL, return empty to drop the attribute
            return '';
        }

        // Only allow https
        if (strtolower($parsedUrl['scheme']) !== 'https') {
            return '';
        }

        $host = strtolower($parsedUrl['host']);
        
        // Check if host is allowed
        if (!in_array($host, self::ALLOWED_IFRAME_HOSTS, true)) {
            return '';
        }

        // Check if path matches allowed prefixes for this host
        if (isset(self::ALLOWED_IFRAME_PATH_PREFIXES[$host])) {
            $path = $parsedUrl['path'] ?? '/';
            $pathAllowed = false;

            foreach (self::ALLOWED_IFRAME_PATH_PREFIXES[$host] as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    $pathAllowed = true;
                    break;
                }
            }

            if (!$pathAllowed) {
                return '';
            }
        }

        // URL is from a trusted provider, allow it
        return $value;
    }
}
