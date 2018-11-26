<?php

/**
 * @license MIT
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Niji\XmlParserBundle\Processor;

/**
 * Interface XmlParsingProcessorInterface.
 */
interface XmlParsingProcessorInterface
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
    public function process($value, array $config = []);
}
