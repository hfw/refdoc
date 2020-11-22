<?php

namespace Helix\RefDoc;

use ReflectionException;
use ReflectionMethod;

/**
 * Extracts info from a method.
 *
 * TODO auto-type based on method name
 */
class RefMethod extends ReflectionMethod implements MemberInterface {

    use RefMemberTrait;

    /**
     * @var MagicType
     */
    protected $return;

    /**
     * @param ReflectionMethod $method
     * @return null|static Returns `null` for opaque methods, e.g. Closure::__invoke()
     */
    public static function from (ReflectionMethod $method) {
        try {
            return $method instanceof static ? $method : new static($method->class, $method->name);
        }
        catch (ReflectionException $exception) {
            return null;
        }
    }

    public function __toString (): string {
        return "{$this->class}::{$this->name}()";
    }

    /**
     * @return RefParam[]
     */
    public function getParameters () {
        return array_map([RefParam::class, 'from'], parent::getParameters());
    }

    /**
     * Checks for a `return` tag in the docblock, falls back to checking the reflection.
     *
     * @return MagicType
     */
    public function getReturnType () {
        if ($this->return) {
            return $this->return;
        }
        // prefer @return over declaration.
        if (preg_match('/^\h*\*\h*@return\h+(?<T>\S+)/im', $this->getDocComment(), $return)) {
            return $this->return = new MagicType($return['T']);
        }
        elseif (preg_match('/^\h*\*\h*(@return\h+.*)$/m', $this->getDocComment(), $invalid)) {
            Log::verbose("{$this} {$invalid[1]}", ~LOG_ERR);
        }
        if (!$type = parent::getReturnType()) {
            Log::verbose("{$this} Assuming mixed return type.", ~LOG_WARNING);
            $this->warnings[] = "This has no documented return type. Assumed <code>mixed</code>.";
        }
        return $this->return = MagicType::from($type);
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::member('>', "{$this->name}()");
        $parents = array_map('strval', $this->getParents());
        Log::verboseMember('^', $parents);
        $xml->startElement('method', Log::verbose([
            'fqn' => $this,
            'name' => $this->name,
            'abstract' => $this->isAbstract(),
            'final' => $this->isFinal(),
            'visibility' => $this->getVisibility(),
            'static' => $this->isStatic(),
            'generator' => $this->isGenerator()
        ]));
        $xml->writeList('parents', 'method', $parents);
        $xml->writeList('params', null, $this->getParameters(), ['variadic' => $this->isVariadic()]);
        Log::verbose('return', LOG_DEBUG);
        $xml->writeElement('return', $this->getReturnType());
        $this->writeDocBlock($xml, $this->getDocComment());
        $xml->endElement();
        Log::verbose();
    }

}