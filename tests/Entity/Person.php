<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Person
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;
}