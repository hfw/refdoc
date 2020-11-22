<?php

namespace Helix\RefDoc;

/**
 * A magic member.
 *
 * TODO: resolve class imports for types
 */
abstract class AbstractMagicMember implements MemberInterface {

    use DocBlockTrait;

    /**
     * Extracts magic members of the called type from a root entity's docblock.
     *
     * @param AbstractRefClass $owner
     * @param string $docblock Extracted magic members are removed.
     * @return static[]
     */
    abstract public static function fromDocBlock (AbstractRefClass $owner, string &$docblock);

    /**
     * @var int
     */
    public $logLevel = LOG_USER;

    /**
     * @var string
     */
    public $docblock = '';

    /**
     * @var string
     */
    public $fqn = '';

    /**
     * @var string
     */
    public $name = '';

    public function __toString (): string {
        return $this->fqn;
    }

    /**
     * Magic members are always `public`.
     *
     * @return string
     */
    final public function getVisibility (): string {
        return 'public';
    }

    /**
     * Cleans up a fragmented magic member comment from the parser and sets it as the docblock.
     *
     * @param string $docblock
     */
    protected function setDocBlock (string $docblock): void {
        if (strlen($docblock = trim($docblock))) {
            // strip leading * if it doesn't start on the initial line.
            $docblock = preg_replace('/^\*\h*/', '', $docblock);
            // straighten up to 2 spaces of indentation
            $docblock = preg_replace('/^\h*\*\h{0,2}/m', ' * ', $docblock);
            $docblock = "/**\n * {$docblock}\n */";
            $this->docblock = $docblock;
        }
    }
}