<?php

namespace Helix\RefDoc;

use ReflectionProperty;

class RefProperty extends ReflectionProperty implements MemberInterface {

    use RefMemberTrait;

    /**
     * @var MagicType
     */
    protected $type;

    /**
     * @param ReflectionProperty $prop
     * @return static
     */
    public static function from (ReflectionProperty $prop) {
        return $prop instanceof static ? $prop : new static($prop->class, $prop->name);
    }

    public function __toString (): string {
        return "{$this->class}::\${$this->name}";
    }

    /**
     * Checks for a `var` tag in the docblock, falls back to checking the default value.
     *
     * @return MagicType
     */
    public function getType () {
        if ($this->type) {
            return $this->type;
        }
        // prefer @var over declaration.
        if (preg_match('/^\h*\*\h*@var\h+(?<T>\S+)/im', $this->getDocComment(), $var)) {
            return $this->type = new MagicType($var['T']);
        }
        elseif (preg_match('/^\h*\*\h*(@var\h+.*)$/m', $this->getDocComment(), $invalid)) {
            Log::verbose("{$this} {$invalid[1]}", ~LOG_ERR);
        }
        // check default value.
        $value = $this->getDeclaringClass()->getDefaultProperties()[$this->name];
        if (isset($value)) {
            return $this->type = new MagicType(Util::toShortType(gettype($value)));
        }
        Log::verbose("{$this} assuming mixed", ~LOG_WARNING);
        return $this->type = new MagicType('mixed');
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::member('$', $this->name);
        $parents = array_map('strval', $this->getParents());
        Log::verboseMember('^', $parents);
        $xml->startElement('property', Log::verbose([
            'fqn' => $this,
            'name' => $this->name,
            'visibility' => $this->getVisibility(),
            $this->isStatic() ? 'static' : null,
        ]));
        $this->getType()->write($xml);
        $value = $this->getDeclaringClass()->getDefaultProperties()[$this->name];
        if (isset($value)) {
            $xml->writeElement('default', Util::toExport($value), [], true);
        }
        $xml->writeList('parents', 'property', $parents);
        $this->writeDocBlock($xml, $this->getDocComment());
        $xml->endElement();
        Log::verbose();
    }

}