<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Sean Molenaar <sean@seanmolenaar.eu>
 * @copyright 2018 Sean Molenaar
 */

namespace OCA\News\Utility;

use \OCP\ILogger;

/**
 * This is a wrapper to make OC\Log conform to Psr\Log\LoggerInterface
 *
 * @package OCA\News\Utility
 */
class PsrLogger implements \Psr\Log\LoggerInterface
{
    private $logger;
    private $appName;

    /**
     * PsrLogger constructor.
     *
     * @param ILogger $logger  The logger
     * @param string  $appName Name of the app
     */
    public function __construct(ILogger $logger, $appName)
    {
        $this->logger  = $logger;
        $this->appName = $appName;
    }

    public function logException($exception, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->logException($exception, $context);
    }

    public function emergency($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $context['app'] = $this->appName;
        $this->logger->log($level, $message, $context);
    }
}
