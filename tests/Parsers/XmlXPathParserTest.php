<?php

namespace Niji\XmlParserBundle\tests\Parsers;

use Monolog\Logger;
use Niji\XmlParserBundle\Parsers\XmlXPathParser;
use Niji\XmlParserBundle\tests\Entity\Person3;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class XmlXPathParserTest extends TestCase
{
    /**
     * @var \Niji\XmlParserBundle\Parsers\XmlXPathParser
     */
    protected $xmlParser;

    protected function setUp()
    {
        /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
        $container = new ContainerBuilder();
        $logger = new Logger('main');

        $loader = new YamlFileLoader(
          $container,
          new FileLocator(__DIR__.'/Resources')
        );
        $loader->load('xml_parsers.yaml');

        $this->xmlParser = new XmlXPathParser($container, $logger);
    }

    public function testPropertyParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/Test.XML'));

        $persons = $this->xmlParser->parse($xmlStr, 'test');

        $this->assertNotEmpty($persons, 'No partners parsed');
        $this->assertCount(3, $persons, 'Invalid number of partners found');
        $this->assertEquals('Name', $persons[0]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName', $persons[0]->get('firstname'),'Unexpected firstname value');
        $this->assertEquals('Name2', $persons[1]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName2', $persons[1]->get('firstname'),'Unexpected firstname value');
        $this->assertEquals('Name3', $persons[2]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName3', $persons[2]->get('firstname'),'Unexpected firstname value');
    }

    public function testPropertyProcessor()
    {

        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/Test2.XML'));

        $persons = $this->xmlParser->parse($xmlStr, 'test2');

        $this->assertNotEmpty($persons, 'No partners parsed');
        $this->assertCount(3, $persons, 'Invalid number of partners found');
        $this->assertEquals('Name', $persons[0]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName', $persons[0]->get('firstname'),'Unexpected firstname value');
        $this->assertEquals('World', $persons[1]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName2', $persons[1]->get('firstname'),'Unexpected firstname value');
        $this->assertEquals('Name3', $persons[2]->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName3', $persons[2]->get('firstname'),'Unexpected firstname value');
    }

    public function testChildrenParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/Test3.XML'));

        $person = $this->xmlParser->parse($xmlStr, 'test3');

        $this->assertNotEmpty($person, 'No partners parsed');
        $this->assertInstanceOf(Person3::class, $person, 'Invalid class for test3 first object');
        $this->assertCount(2, $person->get('children'), 'Invalid number of partners found');
        $this->assertEquals('Name', $person->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName', $person->get('firstname'),'Unexpected firstname value');
        $this->assertEquals('SonName', $person->get('children')[0]->get('name'),'Unexpected son name value');
        $this->assertEquals('SonFirstname', $person->get('children')[0]->get('firstname'),'Unexpected son firstname value');
        $this->assertEquals('SonName2', $person->get('children')[1]->get('name'),'Unexpected second son name value');
        $this->assertEquals('SonFirstname2', $person->get('children')[1]->get('firstname'),'Unexpected second son firstname value');
    }

    public function testArrayParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/Test.XML'));

        $persons = $this->xmlParser->parse($xmlStr, 'test_array');

        $this->assertNotEmpty($persons, 'No partners parsed');
        $this->assertCount(3, $persons, 'Invalid number of partners found');
        $this->assertEquals('Name', $persons[0]['name'],'Unexpected name value');
        $this->assertEquals('FirstName', $persons[0]['firstname'],'Unexpected firstname value');
        $this->assertEquals('Name2', $persons[1]['name'],'Unexpected name value');
        $this->assertEquals('FirstName2', $persons[1]['firstname'],'Unexpected firstname value');
        $this->assertEquals('Name3', $persons[2]['name'],'Unexpected name value');
        $this->assertEquals('FirstName3', $persons[2]['firstname'],'Unexpected firstname value');
    }

    public function testChildrenArrayParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/Test3.XML'));

        $person = $this->xmlParser->parse($xmlStr, 'test_array_children');

        $this->assertNotEmpty($person, 'No partners parsed');
        $this->assertTrue(is_array($person), 'Invalid return type');
        $this->assertCount(2, $person['children'], 'Invalid number of partners found');
        $this->assertEquals('Name', $person['name'],'Unexpected name value');
        $this->assertEquals('FirstName', $person['firstname'],'Unexpected firstname value');
        $this->assertEquals('SonName', $person['children'][0]['name'],'Unexpected son name value');
        $this->assertEquals('SonFirstname', $person['children'][0]['firstname'],'Unexpected son firstname value');
        $this->assertEquals('SonName2', $person['children'][1]['name'],'Unexpected second son name value');
        $this->assertEquals('SonFirstname2', $person['children'][1]['firstname'],'Unexpected second son firstname value');
    }
}
