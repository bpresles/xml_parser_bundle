<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Toy
{
    use XmlParsingTrait;

    protected $name;
}