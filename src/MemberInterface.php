<?php

namespace Helix\RefDoc;

/**
 * A QOL interface to keep all the member types consistent.
 */
interface MemberInterface extends XMLWriterInterface {

    /**
     * @return string Globally unique `<ClassFQN>::<identifier>`
     */
    public function __toString (): string;

    /**
     * @return string `public`, `protected`, `private`
     */
    public function getVisibility (): string;

    /**
     * @return bool Loose for Reflection compatibility.
     */
    public function isStatic ();

}