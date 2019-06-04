<?php

namespace Niji\XmlParserBundle\tests\Parsers;

use Monolog\Logger;
use Niji\XmlParserBundle\Parsers\XmlXPathParser;
use Niji\XmlParserBundle\tests\Entity\Child;
use Niji\XmlParserBundle\tests\Entity\Person3;
use Niji\XmlParserBundle\tests\Entity\Person4;
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

    public function testMultipleChildrenParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/TestMultipleChildren.XML'));

        $person = $this->xmlParser->parse($xmlStr, 'test_multiple_children');

        $this->assertNotEmpty($person, 'No partners parsed');
        $this->assertInstanceOf(Person4::class, $person, 'Invalid class type');
        $this->assertCount(2, $person->get('children'), 'Invalid number of partners found');
        $this->assertEquals('Name', $person->get('name'),'Unexpected name value');
        $this->assertEquals('FirstName', $person->get('firstname'),'Unexpected firstname value');

        $this->assertEquals('SonName', $person->get('children')[0]->get('name'),'Unexpected son name value');
        $this->assertEquals('SonFirstname', $person->get('children')[0]->get('firstname'),'Unexpected son firstname value');
        $this->assertCount(2, $person->get('children')[0]->get('toys'));
        $this->assertEquals('Spiderman', $person->get('children')[0]->get('toys')[0]->get('name'), 'Invalid toy name');
        $this->assertEquals('GameGear', $person->get('children')[0]->get('toys')[1]->get('name'), 'Invalid toy name');

        $this->assertEquals('SonName2', $person->get('children')[1]->get('name'),'Unexpected second son name value');
        $this->assertEquals('SonFirstname2', $person->get('children')[1]->get('firstname'),'Unexpected second son firstname value');
        $this->assertCount(2, $person->get('children')[1]->get('toys'));
        $this->assertEquals('Buzz l\'éclair', $person->get('children')[1]->get('toys')[0]->get('name'), 'Invalid toy name');
        $this->assertEquals('Gameboy', $person->get('children')[1]->get('toys')[1]->get('name'), 'Invalid toy name');

        $this->assertCount(2, $person->get('brothers'), 'Invalid brothers count');
        $this->assertEquals('BrotherName', $person->get('brothers')[0]->get('name'), 'Invalid brother name');
        $this->assertEquals('BrotherFirstname', $person->get('brothers')[0]->get('firstname'), 'Invalid brother firstname');
        $this->assertEquals('BrotherName2', $person->get('brothers')[1]->get('name'), 'Invalid brother name');
        $this->assertEquals('BrotherFirstname2', $person->get('brothers')[1]->get('firstname'), 'Invalid brother firstname');

        $this->assertCount(2, $person->get('uncles'), 'Invalid uncles count');
        $this->assertEquals('UncleName', $person->get('uncles')[0]->get('name'), 'Invalid uncle name');
        $this->assertEquals('UncleFirstname', $person->get('uncles')[0]->get('firstname'), 'Invalid uncle firstname');
        $this->assertEquals('UncleName2', $person->get('uncles')[1]->get('name'), 'Invalid v name');
        $this->assertEquals('UncleFirstname2', $person->get('uncles')[1]->get('firstname'), 'Invalid uncle firstname');
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

    public function testMultipleChildrenArrayParse()
    {
        $xmlStr = file_get_contents(realpath(__DIR__.'/Resources/input/TestMultipleChildren.XML'));

        $person = $this->xmlParser->parse($xmlStr, 'test_array_multiple_children');

        $this->assertNotEmpty($person, 'No partners parsed');
        $this->assertCount(2, $person['children'], 'Invalid number of partners found');
        $this->assertEquals('Name', $person['name'],'Unexpected name value');
        $this->assertEquals('FirstName', $person['firstname'],'Unexpected firstname value');

        $this->assertEquals('SonName', $person['children'][0]['name'],'Unexpected son name value');
        $this->assertEquals('SonFirstname', $person['children'][0]['firstname'],'Unexpected son firstname value');
        $this->assertCount(2, $person['children'][0]['toys']);
        $this->assertEquals('Spiderman', $person['children'][0]['toys'][0]['name'], 'Invalid toy name');
        $this->assertEquals('GameGear', $person['children'][0]['toys'][1]['name'], 'Invalid toy name');

        $this->assertEquals('SonName2', $person['children'][1]['name'],'Unexpected second son name value');
        $this->assertEquals('SonFirstname2', $person['children'][1]['firstname'],'Unexpected second son firstname value');
        $this->assertCount(2, $person['children'][1]['toys']);
        $this->assertEquals('Buzz l\'éclair', $person['children'][1]['toys'][0]['name'], 'Invalid toy name');
        $this->assertEquals('Gameboy', $person['children'][1]['toys'][1]['name'], 'Invalid toy name');

        $this->assertCount(2, $person['brothers'], 'Invalid brothers count');
        $this->assertEquals('BrotherName', $person['brothers'][0]['name'], 'Invalid brother name');
        $this->assertEquals('BrotherFirstname', $person['brothers'][0]['firstname'], 'Invalid brother firstname');
        $this->assertEquals('BrotherName2', $person['brothers'][1]['name'], 'Invalid brother name');
        $this->assertEquals('BrotherFirstname2', $person['brothers'][1]['firstname'], 'Invalid brother firstname');

        $this->assertCount(2, $person['uncles'], 'Invalid uncles count');
        $this->assertEquals('UncleName', $person['uncles'][0]['name'], 'Invalid uncle name');
        $this->assertEquals('UncleFirstname', $person['uncles'][0]['firstname'], 'Invalid uncle firstname');
        $this->assertEquals('UncleName2', $person['uncles'][1]['name'], 'Invalid v name');
        $this->assertEquals('UncleFirstname2', $person['uncles'][1]['firstname'], 'Invalid uncle firstname');
    }
}
