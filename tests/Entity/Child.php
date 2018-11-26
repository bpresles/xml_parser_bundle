<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Child
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;
}