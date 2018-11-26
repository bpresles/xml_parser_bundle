<?php

/**
 * @license MIT
 * @license https://opensource.org/licenses/MIT The MIT License
 */

namespace Niji\XmlParserBundle\Parsers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Niji\XmlParserBundle\Processor\XmlParsingProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class XmlXPathParser.
 *
 * XML parser that uses mapping with XPath paths.
 */
class XmlXPathParser
{
    /**
     * List of mappings.
     *
     * @var array
     */
    protected $mappings;

    /**
     * Doctrine's Entity Manager.
     *
     * @var EntityManager
     */
    protected $em;

    /**
     * Logger service.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * XmlXPathParser constructor.
     *
     * @param ContainerInterface     $container
     *   The parameters service.
     * @param LoggerInterface        $logger
     *   The logging service.
     * @param EntityManagerInterface $em
     *   The entity manager service.
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, EntityManagerInterface $em = null)
    {
        $this->mappings = $container->getParameterBag()->get('xml_parsers.mappings');
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Parse an XML file according to the passed mapping.
     *
     * @param string $xmlStr
     *   XML source content.
     * @param string $mappingName
     *   Name of the mapping as defined in xml_parsers.yaml.
     *
     * @return \Niji\XmlParserBundle\XmlParsingTrait|array $result
     *   A parsed class or array of class
     */
    public function parse(string $xmlStr, string $mappingName)
    {
        $mapping = $this->mappings[$mappingName];

        $destinationClass = !empty($mapping['destination_class']) ? $mapping['destination_class'] : null;
        $result = $this->xPathParse($xmlStr, $mapping['base_root'], $mapping['mapping'], $destinationClass);

        $this->logger->debug('Finished parsing of '.$mappingName.' successfully');

        return $result;
    }

    /**
     * Recursive xPath based Xml crawler.
     *
     * @param string            $xmlStr
     *   The source content.
     * @param string            $baseRoot
     *   The base XML node
     * @param array             $mapping
     *   The mapping configuration for the current object
     * @param string            $destinationClassName
     *   The destination class to use.
     * @param \SimpleXMLElement $xmlDoc
     *   The current XML Element (used for recursive calls).
     *
     * @return \Niji\XmlParserBundle\XmlParsingTrait|array $result
     *   The fully loaded class or array of class or associative array.
     */
    private function xPathParse(string $xmlStr, string $baseRoot, array $mapping, string $destinationClassName = null, \SimpleXMLElement $xmlDoc = null)
    {
        $result = null;
        $type = 'array';
        $isDoctrine = false;
        if (!empty($destinationClassName)) {
            $type = 'object';
            $isDoctrine = $this->isDoctrineEntity($destinationClassName);
        }

        $xmlDoc = $this->loadXMLDocument($xmlStr, $xmlDoc);
        $children = $xmlDoc->xpath($baseRoot);
        $this->logger->debug('Parsing children of : '.$baseRoot);

        $isMultiple = count($children) > 1;
        foreach ($children as $child) {
            /** @var \Niji\XmlParserBundle\XmlParsingTrait | array $subresult */
            $subresult = !empty($destinationClassName) ? new $destinationClassName() : [];

            foreach ($mapping as $destination => $source) {
                if ('array' === $type || (isset($subresult) && $subresult->hasProperty($destination))) {
                    $value = $this->parseChild($xmlStr, $source, $child);

                    $type === 'object' ? $subresult->set($destination, $value) : $subresult[$destination] = $value;
                    $this->logger->debug('Saved '.$destination.' field');
                } else {
                    $this->logger->warning('Missing property '.$destination.' in class '.$destinationClassName);
                }
            }
            $this->updateResult($isMultiple, $isDoctrine, $subresult, $result);
        }

        return $result;
    }

    /**
     * Load the XML document.
     *
     * @param string $xmlStr
     *   Source XML string.
     * @param null   $xmlDoc
     *   Existing XML document (used for recursive calls).
     *
     * @return null|\SimpleXMLElement
     *   THe XML document parsed through SimpleXML.
     */
    private function loadXMLDocument(string $xmlStr, $xmlDoc = null)
    {
        if (!isset($xmlDoc)) {
            $xmlDoc = simplexml_load_string($xmlStr);

            foreach ($xmlDoc->getDocNamespaces() as $strPrefix => $strNamespace) {
                if (strlen($strPrefix) === 0) {
                    $strPrefix = "default";
                }
                $xmlDoc->registerXPathNamespace($strPrefix, $strNamespace);
            }
        }

        return $xmlDoc;
    }

    /**
     * Parse a child node.
     *
     * @param string            $xmlStr
     *   Source XML document.
     * @param array | string    $source
     *   Source value or subvalue.
     * @param \SimpleXMLElement $child
     *   Child element.
     *
     * @return array|mixed|\Niji\XmlParserBundle\XmlParsingTrait|null|string
     *   Parsed value.
     */
    private function parseChild(string $xmlStr, $source, $child)
    {
        if (!empty($source['mapping'])) {
            $value = $this->xPathParse($xmlStr, $source['base_root'], $source['mapping'], $source['destination_class'], $child);
        } else {
            $value = $this->parseChildValue($xmlStr, $child, $source);
        }

        return $value;
    }

    /**
     * Parse a child value.
     *
     * @param string            $xmlStr
     *   Source XML string.
     * @param \SimpleXMLElement $child
     *   Child element.
     * @param array | string    $source
     *   XPath query or source config array.
     *
     * @return mixed|null|string
     *   The parsed value.
     */
    private function parseChildValue(string $xmlStr, \SimpleXMLElement $child, $source)
    {
        $value = null;

        if (!empty($source['mapping'])) {
            $childDestinationClass = !empty($source['destination_class']) ? $source['destination_class'] : null;
            $value = $this->xPathParse(
                $xmlStr,
                $source['base_root'],
                $source['mapping'],
                $childDestinationClass,
                $child
            );
        } else {
            $childXPath = !empty($source['processor']) ? $source['source'] : $source;
            $this->logger->debug('Parsing '.$childXPath.' field');

            $values = $child->xpath($childXPath);
            $value = !empty($values) ? ''.$values[0] : null;

            if (!empty($source['processor'])) {
                $processorClass = $source['processor'];
                $config = isset($source['config']) ? $source['config'] : [];

                /** @var XmlParsingProcessorInterface $processor */
                $processor = new $processorClass();

                $this->logger->debug('Processing '.$value.' field using '.$source['processor']);
                $value = $processor->process($value, $config);
            }
        }

        return $value;
    }

    /**
     * Set the result value depending on passed parameters.
     *
     * @param boolean                                                                                 $isMultiple
     *   When there are more than one XML nodes.
     * @param boolean                                                                                 $isDoctrine
     *   Whether the destination class is a doctrine entity.
     * @param \Niji\XmlParserBundle\XmlParsingTrait | array                                           $value
     *   The value to add to the result.
     * @param \Niji\XmlParserBundle\XmlParsingTrait | \Niji\XmlParserBundle\XmlParsingTrait[] | array $result
     *   The existing $result (if exists).
     */
    private function updateResult(bool $isMultiple, bool $isDoctrine, $value, &$result = null)
    {
        if ($isMultiple) {
            if (false !== $isDoctrine) {
                $result = !isset($result) ? new ArrayCollection() : $result;
                $result->add($value);
            } else {
                $result = !isset($result) ? [] : $result;
                $result[] = $value;
            }
        } else {
            $result = $value;
        }
    }

    /**
     * Check whether the passed class is a Doctrine entity.
     *
     * @param string $className
     *   Class name.
     *
     * @return boolean
     *   Whether it's a Doctrine entity or not.
     */
    private function isDoctrineEntity(string $className)
    {
        return isset($this->em) ? ! $this->em->getMetadataFactory()->isTransient($className) : false;
    }
}
