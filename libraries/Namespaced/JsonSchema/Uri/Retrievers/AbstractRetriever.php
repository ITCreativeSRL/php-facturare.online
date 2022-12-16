<?php
/**
 * FacturareOnline\JsonSchema
 *
 * @filesource
 */

namespace FacturareOnline\JsonSchema\Uri\Retrievers;

/**
 * AbstractRetriever implements the default shared behavior
 * that all descendant Retrievers should inherit
 *
 * @author Steven Garcia <webwhammy@gmail.com>
 */
abstract class AbstractRetriever implements UriRetrieverInterface
{
    /**
     * Media content type
     *
     * @var string
     */
    protected $contentType;

    /**
     * {@inheritdoc}
     *
     * @see \FacturareOnline\JsonSchema\Uri\Retrievers\UriRetrieverInterface::getContentType()
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
