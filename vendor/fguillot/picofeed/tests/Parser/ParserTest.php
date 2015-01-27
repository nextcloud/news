<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testChangeHashAlgo()
    {
        $parser = new Rss20('');
        $this->assertEquals('fb8e20fc2e4c3f248c60c39bd652f3c1347298bb977b8b4d5903b85055620603', $parser->generateId('a', 'b'));

        $parser->setHashAlgo('sha1');
        $this->assertEquals('da23614e02469a0d7c7bd1bdab5c9c474b1904dc', $parser->generateId('a', 'b'));
    }

    public function testLangRTL()
    {
        $this->assertFalse(Parser::isLanguageRTL('fr-FR'));
        $this->assertTrue(Parser::isLanguageRTL('ur'));
        $this->assertTrue(Parser::isLanguageRTL('syr-**'));
        $this->assertFalse(Parser::isLanguageRTL('ru'));
    }

    public function testNamespaceValue()
    {
        $xml = XmlParser::getSimpleXml(file_get_contents('tests/fixtures/rue89.xml'));
        $this->assertNotFalse($xml);
        $namespaces = $xml->getNamespaces(true);

        $parser = new Rss20('');
        $this->assertEquals('Blandine Grosjean', XmlParser::getNamespaceValue($xml->channel->item[0], $namespaces, 'creator'));
        $this->assertEquals('Pierre-Carl Langlais', XmlParser::getNamespaceValue($xml->channel->item[1], $namespaces, 'creator'));
    }
}
