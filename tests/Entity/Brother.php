<?php

namespace Niji\XmlParserBundle\tests\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class Brother
{
    use XmlParsingTrait;

    protected $name;

    protected $firstname;

}