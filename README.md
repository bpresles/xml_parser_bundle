# XML Parser Bundle

This bundle allows easy parsing using XPath.

## Installation

`composer req niji/xml-parser-bundle`


## XPath parsing

To parse an XPath file, you need to:

1) Create a parameter YAML file like this:

````yaml
parameters:
  xml_parser.mappings:
    mapping_a:
      destination_class: 'App\Entity\EntityName'
      base_root: '//namespace:XMLNode/XMLSubNode'
      mapping:
        property_dest: 'source_property_name'
        property_dest2: 'source_property2_name'
        sub_entity:
          destination_class: 'App\Entity\SubEntityName'
          base_root: 'XMLNodeName'
          mapping:
            sub_entity_property: 'source_subentity_property'
            ...
        ...
````

If your XML have `""` as default namespace, use `default` as namespace name for your XPath queries.

The `destination_class` key is optional, if no destination class is specified the parser will return an associative `array` as result.

2) Add the `Niji\XmlParserBundle\XmlParsingTrait` to your destination class:

```php
<?php

namespace App\Entity;

use Niji\XmlParserBundle\XmlParsingTrait;

class EntityName
{
    
    use XmlParsingTrait;
    
    ...
}
```

3) Use the parser in your Symfony custom code as follow:

````php
<?php

use Niji\XmlParserBundle\Parsers\XmlXPathParser;

class YourClass
{
    /**
    * @var \Niji\XmlParserBundle\Parsers\XmlXPathParser
    */
    protected $parser;
        
    /**
     * Inject the parser use Symfony's dependency injection.
     *
     * @param \Niji\XmlParserBundle\Parsers\XmlXPathParser $parser
     */
    public function __construct(XmlXPathParser $parser) {
        $this->parser = $parser;
    }
    
    /**
     * Parsing method.
     * 
     * @param string $sourceUrl
     * @param string $mappingName
     */
    public function yourMethod(string $sourceUrl, string $mappingName) {
        $xmlStr = file_get_contents($sourceUrl);
    
        // $result is a destination class or an array of destination classes or an associative array.
        $result = $this->parser->parse($xmlStr, $mappingName);
    }
}

````

## Processors

Sometimes you will need to transform the input data (e.g: Date formatting, type casting (string to boolean)...etc).

In that case, you can use a `Processor` class that implements the `XmlParsingProcessorInterface`:

````php
<?php

namespace App\XmlProcessors;

use Niji\XmlParserBundle\Processor\XmlParsingProcessorInterface;

class BooleanProcessor implements XmlParsingProcessorInterface 
{

    /**
     * Process the passed value
     *
     * @param mixed $value
     *   Source value.
     * @param array $config
     *   Processor configuration.
     *
     * @return mixed
     *   Processed value.
     */
    public function process($value, array $config = []) {
        $processedValue = null;
        
        // Do whatever you need to process the $value
        // e.g: $processedValue = (boolean)$value;
        
        return $processedValue;
    }
}

````

Then indicate in the mapping the fully qualified name of your processor as follow:

````yaml
parameters:
  xml_parser.mappings:
    mapping_a:
      destination_class: 'App\Entity\EntityName'
      base_root: '//namespace:XMLNode/XMLSubNode'
      mapping:
        property_dest:
          source: 'source_property_name'
          processor: 'App\XmlProcessors\BooleanProcessor'
          config:
            config_key: 'config_value'
            config_key2: 'config_value2'
            ...
````