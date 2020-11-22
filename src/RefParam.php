<?php

namespace Helix\RefDoc;

use ReflectionParameter;

class RefParam extends ReflectionParameter implements XMLWriterInterface {

    /**
     * @param ReflectionParameter $param
     * @return static
     */
    public static function from (ReflectionParameter $param) {
        if ($param instanceof static) {
            return $param;
        }
        $class = $param->getDeclaringClass()->name;
        $method = $param->getDeclaringFunction()->name;
        $hack = static::class;
        return new $hack([$class, $method], $param->name); // php lacks documentation.
    }

    public function __toString (): string {
        return "{$this->name}";
    }

    /**
     * @return RefClass
     */
    public function getClass () {
        return RefClass::from(parent::getClass());
    }

    /**
     * @return RefClass
     */
    public function getDeclaringClass () {
        return RefClass::from(parent::getDeclaringClass());
    }

    /**
     * @return MagicType
     */
    public function getType () {
        return MagicType::from(parent::getType());
    }

    public function write (RefXML $xml): void {
        Log::verbose('param', LOG_DEBUG);
        $xml->startElement('param', Log::verbose([
            'name' => $this->name,
            'position' => $this->getPosition(),
            'nullable' => $this->allowsNull(),
            'reference' => $this->isPassedByReference(),
        ]));
        $this->getType()->write($xml);
        Log::verbose();
        if ($this->isDefaultValueAvailable()) {
            $xml->writeAttributes(Log::verbose([
                'default' => $this->isDefaultValueConstant()
                    ? $this->getDefaultValueConstantName()
                    : Util::toExport($this->getDefaultValue())
            ]));
            Log::verbose();
        }
        $xml->endElement();
    }
}