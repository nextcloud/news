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

use OCA\News\Vendor\Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use OCA\News\Vendor\Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use OCA\News\Utility\SafeIframeAttributeSanitizer;

/**
 * HTML Sanitizer wrapper for News app
 *
 * This class provides HTML sanitization with the same rules as the previous
 * HTMLPurifier configuration:
 * - Forbid class attributes
 * - Allow safe iframes for YouTube, Vimeo, VK
 * - Allow specific URI schemes: http, https, data, mailto, ftp, nntp, news, tel
 * - Allow allowfullscreen attribute on iframes
 */
class HtmlSanitizer
{
    private HtmlSanitizerInterface $sanitizer;

    public function __construct(HtmlSanitizerInterface $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    /**
     * Sanitize HTML content
     *
     * @param string $html Raw HTML content
     * @return string Sanitized HTML content
     */
    public function purify(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }

    /**
     * Create a configured HTML sanitizer instance with News app rules
     *
     * @return HtmlSanitizerInterface
     */
    public static function createSanitizer(): HtmlSanitizerInterface
    {
        $config = (new HtmlSanitizerConfig())
            // Start with safe elements (includes common HTML tags like p, div, a, img, etc.)
            ->allowSafeElements()
            // Allow iframe for YouTube, Vimeo, VK embeds
            ->allowElement('iframe', [
                'src', 'width', 'height', 'frameborder',
                'allowfullscreen', 'allow', 'title'
            ])
            // Configure allowed URI schemes for links
            ->allowLinkSchemes(['http', 'https', 'mailto', 'ftp', 'nntp', 'news', 'tel'])
            // Configure allowed URI schemes for media (img, video, audio)
            ->allowMediaSchemes(['http', 'https', 'data'])
            // Allow relative links and media
            ->allowRelativeLinks(true)
            ->allowRelativeMedias(true)
            // Add custom iframe src sanitizer to restrict to YouTube, Vimeo, VK
            ->withAttributeSanitizer(new SafeIframeAttributeSanitizer());

        // Remove class attribute from all elements (by not allowing it)
        // This is implicitly done by allowSafeElements() which doesn't include 'class'
        // in the safe attributes list by default

        return new \OCA\News\Vendor\Symfony\Component\HtmlSanitizer\HtmlSanitizer($config);
    }
}
