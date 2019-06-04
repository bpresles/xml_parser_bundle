<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Uncle
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;
}