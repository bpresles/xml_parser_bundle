<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Person3
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;

    protected $children;
}