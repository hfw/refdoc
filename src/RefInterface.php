<?php

namespace Helix\RefDoc;

class RefInterface extends AbstractRefClass {

    public function write (RefXML $xml): void {
        Log::entity($this);
        $xml->startElement('interface');
        $this->writeAttributes($xml);
        $this->writeParents($xml);
        $this->writeInherited($xml);
        $this->writeDocBlock($xml, $this->getDocComment());
        $this->writeConstants($xml);
        $this->writeMethods($xml);
        $xml->endElement();
        Log::line();
    }
}