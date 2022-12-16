<?php

/*
 * This file is part of the FacturareOnline\JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FacturareOnline\JsonSchema;

/**
 * @package FacturareOnline\JsonSchema
 */
interface UriResolverInterface
{
    /**
     * Resolves a URI
     *
     * @param string      $uri     Absolute or relative
     * @param null|string $baseUri Optional base URI
     *
     * @return string Absolute URI
     */
    public function resolve($uri, $baseUri = null);
}
