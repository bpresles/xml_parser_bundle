<?php

/**
 * @license MIT
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Niji\XmlParserBundle;

/**
 * Trait XmlParsingTrait.
 *
 * Adds generic getter/setter and hasProperty methods to objects,
 * used by XML Parser.
 */
trait XmlParsingTrait
{

    /**
     * Generic getter.
     *
     * @param string $name
     *   Property name.
     *
     * @return mixed
     *   Property value.
     */
    public function get(string $name)
    {
        return $this->{$name};
    }

    /**
     * Generic setter.
     *
     * @param string $name
     *   Property name.
     * @param mixed  $value
     *   Property value.
     */
    public function set(string $name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * Check wether property exists.
     *
     * @param string $name
     *   Property name.
     *
     * @return bool
     *   TRUE if it exists, FALSE otherwise.
     */
    public function hasProperty(string $name)
    {
        return property_exists($this, $name);
    }
}
