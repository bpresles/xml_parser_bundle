<?php

namespace Niji\XmlParserBundle\tests\Processor;

use Niji\XmlParserBundle\Processor\XmlParsingProcessorInterface;

class HelloWorldProcessor implements XmlParsingProcessorInterface
{
    /**
     * Process the passed value.
     *
     * @param mixed $value
     *   Source value.
     * @param array $config
     *   Processor configuration.
     *
     * @return mixed
     *   Processed value.
     */
    public function process($value, array $config = [])
    {
        return $value === 'Hello' ? 'World' : $value;
    }
}