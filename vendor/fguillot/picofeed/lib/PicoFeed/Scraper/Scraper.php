<?php

namespace PicoFeed\Scraper;

use PicoFeed\Client\Client;
use PicoFeed\Client\ClientException;
use PicoFeed\Client\Url;
use PicoFeed\Config\Config;
use PicoFeed\Encoding\Encoding;
use PicoFeed\Filter\Filter;
use PicoFeed\Logging\Logger;
use PicoFeed\Parser\XmlParser;

/**
 * Scraper class.
 *
 * @author  Frederic Guillot
 */
class Scraper
{
    /**
     * URL.
     *
     * @var string
     */
    private $url = '';

    /**
     * Relevant content.
     *
     * @var string
     */
    private $content = '';

    /**
     * HTML content.
     *
     * @var string
     */
    private $html = '';

    /**
     * HTML content encoding.
     *
     * @var string
     */
    private $encoding = '';

    /**
     * Flag to enable candidates parsing.
     *
     * @var bool
     */
    private $enableCandidateParser = true;

    /**
     * Config object.
     *
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * Constructor.
     *
     * @param \PicoFeed\Config\Config $config Config class instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        Logger::setTimezone($this->config->getTimezone());
    }

    /**
     * Disable candidates parsing.
     *
     * @return Scraper
     */
    public function disableCandidateParser()
    {
        $this->enableCandidateParser = false;

        return $this;
    }

    /**
     * Get encoding.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set encoding.
     *
     * @param string $encoding
     *
     * @return Scraper
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Get URL to download.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set URL to download.
     *
     * @param string $url URL
     *
     * @return Scraper
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Return true if the scraper found relevant content.
     *
     * @return bool
     */
    public function hasRelevantContent()
    {
        return !empty($this->content);
    }

    /**
     * Get relevant content.
     *
     * @return string
     */
    public function getRelevantContent()
    {
        return $this->content;
    }

    /**
     * Get raw content (unfiltered).
     *
     * @return string
     */
    public function getRawContent()
    {
        return $this->html;
    }

    /**
     * Set raw content (unfiltered).
     *
     * @param string $html
     *
     * @return Scraper
     */
    public function setRawContent($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get filtered relevant content.
     *
     * @return string
     */
    public function getFilteredContent()
    {
        $filter = Filter::html($this->content, $this->url);
        $filter->setConfig($this->config);

        return $filter->execute();
    }

    /**
     * Download the HTML content.
     *
     * @return bool
     */
    public function download()
    {
        if (!empty($this->url)) {

            // Clear everything
            $this->html = '';
            $this->content = '';
            $this->encoding = '';

            try {
                $client = Client::getInstance();
                $client->setConfig($this->config);
                $client->setTimeout($this->config->getGrabberTimeout());
                $client->setUserAgent($this->config->getGrabberUserAgent());
                $client->execute($this->url);

                $this->url = $client->getUrl();
                $this->html = $client->getContent();
                $this->encoding = $client->getEncoding();

                return true;
            } catch (ClientException $e) {
                Logger::setMessage(get_called_class().': '.$e->getMessage());
            }
        }

        return false;
    }

    /**
     * Execute the scraper.
     */
    public function execute()
    {
        $this->download();

        if (!$this->skipProcessing()) {
            $this->prepareHtml();

            $parser = $this->getParser();

            if ($parser !== null) {
                $this->content = $parser->execute();
                Logger::setMessage(get_called_class().': Content length: '.strlen($this->content).' bytes');
            }
        }
    }

    /**
     * Returns true if the parsing must be skipped.
     *
     * @return bool
     */
    public function skipProcessing()
    {
        $handlers = array(
            'detectStreamingVideos',
            'detectPdfFiles',
        );

        foreach ($handlers as $handler) {
            if ($this->$handler()) {
                return true;
            }
        }

        if (empty($this->html)) {
            Logger::setMessage(get_called_class().': Raw HTML is empty');

            return true;
        }

        return false;
    }

    /**
     * Get the parser.
     *
     * @return ParserInterface
     */
    public function getParser()
    {
        $ruleLoader = new RuleLoader($this->config);
        $rules = $ruleLoader->getRules($this->url);

        if (!empty($rules['grabber'])) {
            Logger::setMessage(get_called_class().': Parse content with rules');

            foreach ($rules['grabber'] as $pattern => $rule) {
                $url = new Url($this->url);
                $sub_url = $url->getFullPath();

                if (preg_match($pattern, $sub_url)) {
                    Logger::setMessage(get_called_class().': Matched url '.$sub_url);

                    return new RuleParser($this->html, $rule);
                }
            }
        } elseif ($this->enableCandidateParser) {
            Logger::setMessage(get_called_class().': Parse content with candidates');

            return new CandidateParser($this->html);
        }

        return;
    }

    /**
     * Normalize encoding and strip head tag.
     */
    public function prepareHtml()
    {
        $html_encoding = XmlParser::getEncodingFromMetaTag($this->html);

        $this->html = Encoding::convert($this->html, $html_encoding ?: $this->encoding);
        $this->html = Filter::stripHeadTags($this->html);

        Logger::setMessage(get_called_class().': HTTP Encoding "'.$this->encoding.'" ; HTML Encoding "'.$html_encoding.'"');
    }

    /**
     * Return the Youtube embed player and skip processing.
     *
     * @return bool
     */
    public function detectStreamingVideos()
    {
        if (preg_match("#(?<=v=|v\/|vi=|vi\/|youtu.be\/)[a-zA-Z0-9_-]{11}#", $this->url, $matches)) {
            $this->content = '<iframe width="560" height="315" src="//www.youtube.com/embed/'.$matches[0].'" frameborder="0"></iframe>';

            return true;
        }

        return false;
    }

    /**
     * Skip processing for PDF documents.
     *
     * @return bool
     */
    public function detectPdfFiles()
    {
        return substr($this->url, -3) === 'pdf';
    }
}
