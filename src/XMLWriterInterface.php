<?php

namespace Helix\RefDoc;

/**
 * IoC when content being written by {@see RefXML} can write itself.
 */
interface XMLWriterInterface {

    /**
     * Reflector writes itself to the XML.
     *
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void;
}