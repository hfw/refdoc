<?php

namespace Helix\RefDoc;

/**
 * Classes, Traits, and Interfaces
 */
class RefClass extends AbstractRefClass {

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::entity($this);
        $xml->startElement('class');
        $this->writeAttributes($xml, [
            'abstract' => $this->isAbstract(),
            'final' => $this->isFinal(),
        ]);
        $this->writeParents($xml);
        $this->writeInherited($xml);
        $this->writeDocBlock($xml, $this->getDocComment());
        $this->writeConstants($xml);
        $this->writeProperties($xml);
        $this->writeMethods($xml);
        $xml->endElement();
        Log::line();
    }

}