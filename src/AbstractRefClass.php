<?php

namespace Helix\RefDoc;

use ReflectionClass;
use ReflectionProperty;

abstract class AbstractRefClass extends ReflectionClass implements XMLWriterInterface {

    use DocBlockTrait {
        writeDocBlock_Tags as private _writeDocBlock_Tags;
    }

    /**
     * @param ReflectionClass $entity
     * @return static
     */
    public static function from (ReflectionClass $entity) {
        return $entity instanceof static ? $entity : new static($entity->name);
    }

    /**
     * FQN
     *
     * @return string
     */
    final public function __toString (): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RefConstant
     */
    public function getConstant ($name) {
        return RefConstant::from(parent::getConstant($name));
    }

    /**
     * @return RefConstant[]
     */
    public function getConstants () {
        return array_map([RefConstant::class, 'from'], parent::getConstants());
    }

    /**
     * @return RefMethod
     */
    public function getConstructor () {
        return RefMethod::from(parent::getConstructor());
    }

    /**
     * @return RefConstant[]
     */
    public function getDeclaredConstants () {
        return array_filter($this->getReflectionConstants(), [$this, 'isDeclaring']);
    }

    /**
     * @return RefMethod[]
     */
    public function getDeclaredMethods () {
        return array_filter($this->getMethods(), [$this, 'isDeclaring']);
    }

    /**
     * @return RefProperty[]
     */
    public function getDeclaredProperties () {
        return array_filter($this->getProperties(), [$this, 'isDeclaring']);
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function getDefaultValue (string $property) {
        return $this->getDefaultProperties()[$property];
    }

    /**
     * @return RefConstant[]
     */
    public function getInheritedConstants () {
        if ($constants = array_map([$this, 'getParentsOf'], $this->getReflectionConstants())) {
            return array_merge(...$constants);
        }
        return [];
    }

    /**
     * @return RefMethod[]
     */
    public function getInheritedMethods () {
        if ($methods = array_map([$this, 'getParentsOf'], $this->getMethods())) {
            return array_merge(...$methods);
        }
        return [];
    }

    /**
     * @return RefProperty[]
     */
    public function getInheritedProperties () {
        if ($props = array_map([$this, 'getParentsOf'], $this->getProperties())) {
            return array_merge(...$props);
        }
        return [];
    }

    /**
     * @return RefInterface[]
     */
    public function getInterfaces () {
        return array_map([RefInterface::class, 'from'], parent::getInterfaces());
    }

    /**
     * @param string $name
     * @return null|RefMethod
     */
    public function getMethod ($name) {
        return $this->hasMethod($name) ? RefMethod::from(parent::getMethod($name)) : null;
    }

    /**
     * @param int|null $filter
     * @return RefMethod[]
     */
    public function getMethods ($filter = null) {
        return array_filter(array_map([RefMethod::class, 'from'], parent::getMethods($filter)));
    }

    /**
     * @return null|RefClass
     */
    public function getParentClass () {
        return ($parent = parent::getParentClass()) ? RefClass::from($parent) : null;
    }

    /**
     * Returns all interfaces, traits, and parent class.
     *
     * @return RefClass[]
     */
    public function getParents () {
        return array_merge(
            $this->getTraits(),
            $this->getInterfaces(),
            ($parentClass = $this->getParentClass()) ? [$parentClass] : []
        );
    }

    /**
     * Resolves the parents of a member belonging to the entity.
     *
     * @param RefConstant|RefProperty|RefMethod $member
     * @return RefConstant[]|RefProperty[]|RefMethod[]
     */
    public function getParentsOf ($member) {
        $getMember = function(AbstractRefClass $parent) use ($member) {
            if ($member instanceof RefConstant) {
                return $parent->getReflectionConstant($member->name);
            }
            elseif ($member instanceof RefProperty) {
                return $parent->getProperty($member->name);
            }
            return $parent->getMethod($member->name);
        };
        $parents = [];
        foreach ($this->getParents() as $parent) {
            if ($pMember = $getMember($parent)) {
                // constants have single-inheritance everywhere.
                // climb until not found.
                if ($member instanceof RefConstant) {
                    return $parent->getParentsOf($pMember) ?: [$pMember];
                }
                // don't climb constructors. the immediate parent is preferable.
                if ($member instanceof RefMethod and ($member->isConstructor() or $member->isDestructor())) {
                    return [$pMember];
                }
                // methods have multiple-inheritance from interfaces.
                // properties have multiple-inheritance from traits.
                // climb and merge until not found.
                $parents = array_merge($parents, $parent->getParentsOf($pMember) ?: [$pMember]);
            }
        }
        return array_unique($parents); // squash diamond inheritance
    }

    /**
     * @param int|null $filter
     * @return array|ReflectionProperty[]
     */
    public function getProperties ($filter = null) {
        return array_map([RefProperty::class, 'from'], parent::getProperties($filter));
    }

    /**
     * @param string $name
     * @return null|RefProperty
     */
    public function getProperty ($name) {
        return $this->hasProperty($name) ? RefProperty::from(parent::getProperty($name)) : null;
    }

    /**
     * @param string $name
     * @return null|RefConstant
     */
    public function getReflectionConstant ($name) {
        return $this->hasConstant($name) ? RefConstant::from(parent::getReflectionConstant($name)) : null;
    }

    /**
     * @return RefConstant[]
     */
    public function getReflectionConstants () {
        return array_map([RefConstant::class, 'from'], parent::getReflectionConstants());
    }

    /**
     * @return RefTrait[]
     */
    public function getTraits () {
        return array_map([RefTrait::class, 'from'], parent::getTraits());
    }

    /**
     * @param RefConstant|RefProperty|RefMethod $member
     * @return bool
     */
    protected function isDeclaring ($member): bool {
        if ($member->class === $this->name) {
            if ($parents = $this->getParentsOf($member)) {
                return $parents[0]->getDocComment() !== $member->getDocComment();
            }
            return true;
        }
        return false;
    }

    /**
     * @param RefXML $xml
     * @param string[] $extra
     */
    protected function writeAttributes (RefXML $xml, array $extra = []): void {
        $xml->writeAttributes(Log::verbose([
                'fqn' => $this->name,
                'ns' => $this->getNamespaceName(),
                'name' => $this->getShortName(),
                'file' => $file = $this->getFileName(),
                'modified' => $file ? date(DATE_W3C, filemtime($file)) : null,
                'core' => $this->isInternal(), // avoids conflating it with @internal
                'ext' => (bool)$this->getExtensionName()
            ] + $extra)
        );
    }

    /**
     * @param RefXML $xml
     */
    protected function writeConstants (RefXML $xml): void {
        $xml->writeList('constants', null, $this->getDeclaredConstants());
    }

    /**
     * @param RefXML $xml
     * @param string $docblock
     */
    protected function writeDocBlock_Tags (RefXML $xml, string &$docblock): void {
        $xml->writeList('magicProperties', null, MagicProperty::fromDocBlock($this, $docblock));
        $xml->writeList('magicMethods', null, MagicMethod::fromDocBlock($this, $docblock));
        $this->_writeDocBlock_Tags($xml, $docblock);
    }

    /**
     * @param RefXML $xml
     */
    protected function writeInherited (RefXML $xml): void {
        static $list = [
            'k' => ['constants', 'constant'],
            '$' => ['properties', 'property'],
            '>' => ['methods', 'method']
        ];
        $inherited = array_filter([
            'k' => $this->getInheritedConstants(),
            '$' => $this->getInheritedProperties(),
            '>' => $this->getInheritedMethods()
        ]);
        if ($inherited) {
            $xml->startElement('inherited');
            foreach ($inherited as $code => $members) {
                $members = array_map('strval', $members);
                Log::verboseMember($code, $members);
                $xml->writeList($list[$code][0], $list[$code][1], $members);
            }
            $xml->endElement();
        }
    }

    /**
     * @param RefXML $xml
     */
    protected function writeMethods (RefXML $xml): void {
        $xml->writeList('methods', null, $this->getDeclaredMethods());
    }

    /**
     * @param RefXML $xml
     */
    protected function writeParents (RefXML $xml): void {
        if ($parent = $this->getParentClass()) {
            Log::member('^', $parent->name);
            $xml->writeElement('extends', $parent->name);
        }

        Log::member('i', $interfaces = $this->getInterfaceNames());
        $xml->writeList('implements', 'interface', $interfaces);

        Log::member('u', $traits = $this->getTraitNames());
        $xml->writeList('uses', 'trait', $traits);
    }

    /**
     * @param RefXML $xml
     */
    protected function writeProperties (RefXML $xml): void {
        $xml->writeList('properties', null, $this->getDeclaredProperties());
    }
}