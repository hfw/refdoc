<?php

namespace Helix\RefDoc;

class RefTrait extends AbstractRefClass {

    public function write (RefXML $xml): void {
        Log::entity($this);
        $xml->startElement('trait');
        $this->writeAttributes($xml);
        $this->writeParents($xml);
        $this->writeInherited($xml);
        $this->writeDocBlock($xml, $this->getDocComment());
        $this->writeProperties($xml);
        $this->writeMethods($xml);
        $xml->endElement();
        Log::line();
    }
}