<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Person4
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;

    protected $children;

    protected $brothers;

    protected $uncles;
}