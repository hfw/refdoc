<?php

namespace Helix\RefDoc;

/**
 * Common overrides and functions for reflection members.
 */
trait RefMemberTrait {

    use DocBlockTrait;
    use VisibilityTrait;

    /**
     * Only use this when class composition doesn't matter.
     *
     * @return RefClass
     */
    public function getDeclaringClass () {
        return RefClass::from(parent::getDeclaringClass());
    }

    /**
     * Returns the parents, but only if this member was redeclared from another.
     *
     * @return static[]
     */
    public function getParents () {
        /** @var RefConstant|RefProperty|RefMethod $that */
        $that = $this;
        return $this->getDeclaringClass()->getParentsOf($that);
    }

}