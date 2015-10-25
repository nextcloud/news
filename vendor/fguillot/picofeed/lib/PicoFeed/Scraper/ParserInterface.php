<?php

namespace PicoFeed\Scraper;

interface ParserInterface
{
    /**
     * Execute the parser and return the contents.
     *
     * @return string
     */
    public function execute();
}
