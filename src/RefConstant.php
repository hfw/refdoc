<?php

namespace Helix\RefDoc;

use ReflectionClassConstant;

class RefConstant extends ReflectionClassConstant implements MemberInterface {

    use RefMemberTrait;

    /**
     * @param ReflectionClassConstant $const
     * @return RefConstant
     */
    public static function from (ReflectionClassConstant $const) {
        return $const instanceof static ? $const : new static($const->class, $const->name);
    }

    public function __toString (): string {
        return "{$this->class}::{$this->name}";
    }

    /**
     * All constants have static access.
     *
     * @return bool
     */
    final public function isStatic (): bool {
        return true;
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::member('k', $this->name);
        $value = $this->getValue();
        $type = Util::toShortType(gettype($value));
        $isArray = is_array($value);
        $value = Util::toExport($value);
        $xml->startElement('constant', Log::verbose([
            'fqn' => $this,
            'name' => $this->name,
            'visibility' => $this->getVisibility(),
            'type' => $type
        ]));
        Log::verbose(['value' => $isArray ? "\n" . $value : $value]);
        $xml->writeElement('value', $value, [], true);
        $this->writeDocBlock($xml, $this->getDocComment());
        $xml->endElement();
        Log::verbose();
    }
}