<?php
namespace PicoFeed\Parser;

use DOMDocument;

use PHPUnit_Framework_TestCase;


class XmlParserTest extends PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $this->assertFalse(XmlParser::getDomDocument(''));
        $this->assertFalse(XmlParser::getSimpleXml(''));
        $this->assertNotFalse(XmlParser::getHtmlDocument(''));
    }

    public function testGetEncodingFromMetaTag()
    {
        $this->assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<html><head><meta content="text/html; charset=iso-8859-1" http-equiv="Content-Type"/></head></html>'));
        $this->assertEquals('iso-8859-1', XmlParser::getEncodingFromMetaTag('<html><head><meta charset="iso-8859-1"></head></html>'));
    }

    public function testGetEncodingFromXmlTag()
    {
        $this->assertEquals('utf-8', XmlParser::getEncodingFromXmlTag("<?xml version='1.0' encoding='UTF-8'?><?xml-stylesheet"));
        $this->assertEquals('utf-8', XmlParser::getEncodingFromXmlTag('<?xml version="1.0" encoding="UTF-8"?><feed xml:'));
        $this->assertEquals('windows-1251', XmlParser::getEncodingFromXmlTag('<?xml version="1.0" encoding="Windows-1251"?><rss version="2.0">'));
        $this->assertEquals('', XmlParser::getEncodingFromXmlTag("<?xml version='1.0'?><?xml-stylesheet"));
    }

    public function testScanForXEE()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [<!ENTITY harmless "completely harmless">]>
<results>
    <result>This result is &harmless;</result>
</results>
XML;

        $this->assertFalse(XmlParser::getDomDocument($xml));
    }

    public function testScanForXXE()
    {
        $file = tempnam(sys_get_temp_dir(), 'PicoFeed_XmlParser');
        file_put_contents($file, 'Content Injection');

        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE root
[
<!ENTITY foo SYSTEM "file://$file">
]>
<results>
    <result>&foo;</result>
</results>
XML;

        $this->assertFalse(XmlParser::getDomDocument($xml));
        unlink($file);
    }

    public function testScanSimpleXML()
    {
        return <<<XML
<?xml version="1.0"?>
<results>
    <result>test</result>
</results>
XML;
        $result = XmlParser::getSimpleXml($xml);
        $this->assertTrue($result instanceof SimpleXMLElement);
        $this->assertEquals($result->result, 'test');
    }

    public function testScanDomDocument()
    {
        return <<<XML
<?xml version="1.0"?>
<results>
    <result>test</result>
</results>
XML;
        $result = XmlParser::getDomDocument($xml);
        $this->assertTrue($result instanceof DOMDocument);
        $node = $result->getElementsByTagName('result')->item(0);
        $this->assertEquals($node->nodeValue, 'test');
    }

    public function testScanInvalidXml()
    {
        $xml = <<<XML
<foo>test</bar>
XML;

        $this->assertFalse(XmlParser::getDomDocument($xml));
        $this->assertFalse(XmlParser::getSimpleXml($xml));
    }

    public function testScanXmlWithDTD()
    {
        $xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE results [
<!ELEMENT results (result+)>
<!ELEMENT result (#PCDATA)>
]>
<results>
    <result>test</result>
</results>
XML;

        $result = XmlParser::getDomDocument($xml);
        $this->assertTrue($result instanceof DOMDocument);
        $this->assertTrue($result->validate());
    }
}
