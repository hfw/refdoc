<?php

namespace Helix\RefDoc;

use Countable;
use ReflectionType;

/**
 * A `ReflectionType` that supports unions and other helpful things.
 */
class MagicType extends ReflectionType implements XMLWriterInterface, Countable {

    /**
     * @var string
     */
    protected $def;

    /**
     * @var static[]
     */
    protected $union = [];

    /**
     * @param null|ReflectionType $type Defaults to `mixed`
     * @return static
     */
    public static function from (?ReflectionType $type) {
        return $type instanceof static ? $type : new static($type ?? 'mixed');
    }

    public function __construct (string $def) {
        $this->def = $def = trim($def);
        $this->union = [$this];
        if (count($subtypes = explode('|', $def)) > 1) {
            $this->union = array_map(function(string $def) {
                return new static($def);
            }, $subtypes);
        }
    }

    public function __toString (): string {
        return $this->def;
    }

    /**
     * Whether the type allows arrays.
     *
     * @return bool
     */
    public function allowsArray (): bool {
        return $this->allowsType('/^(array|.*?\[\])$/i');
    }

    /**
     * Whether the type allows generators.
     *
     * @return bool
     */
    public function allowsGenerator (): bool {
        return (bool)preg_match('/^\??\\\\?Generator$/', $this->def);
    }

    /**
     * Whether the type allows `mixed`.
     *
     * @return bool
     */
    public function allowsMixed (): bool {
        return $this->allowsType('/^mixed\b/');
    }

    /**
     * Whether the type allows `?type`, `mixed`, or `null`
     *
     * @return bool
     */
    public function allowsNull (): bool {
        return $this->allowsType('/^(\?|mixed|null|)$/i');
    }

    /**
     * @param string $regex
     * @return bool
     */
    public function allowsType (string $regex): bool {
        if ($this->isUnion()) {
            foreach ($this->union as $type) {
                if ($type->allowsType($regex)) {
                    return true;
                }
            }
            return false;
        }
        return (bool)preg_match($regex, $this->def);
    }

    /**
     * The number of subtypes in the union.
     *
     * @return int
     */
    public function count (): int {
        return count($this->union);
    }

    /**
     * Returns the union component types.
     *
     * @return MagicType[]
     */
    public function getSubtypes () {
        return $this->union;
    }

    public function isBuiltin () {
        return $this->allowsType('/^\??(null|bool|int|float|string|array|object)(\[\])*$/i');
    }

    /**
     * @return bool
     */
    public function isUnion (): bool {
        return $this->count() > 1;
    }

    public function write (RefXML $xml): void {
        Log::verbose(['type' => $this->def]);
        $xml->startElement('type', Log::verbose([
            $this->allowsNull() ? 'nullable' : null,
            $this->isBuiltin() ? 'builtin' : null,
            $this->allowsArray() ? 'array' : null,
            $this->allowsGenerator() ? 'generator' : null,
        ]));
        $xml->writeElement('def', $this->def, [], true);
        Log::verbose();
        if ($this->isUnion()) {
            Log::verbose('union', LOG_DEBUG);
            $xml->writeList('union', null, $this->getSubtypes());
        }
        $xml->endElement();
    }
}