<?php

namespace Helix\RefDoc;

use Reflector;

/**
 * Adds visibility helper to member reflections.
 */
trait VisibilityTrait {

    /**
     * @return string
     */
    public function getVisibility (): string {
        /** @var Reflector $that */
        $that = $this;
        if ($that->isPublic()) {
            return 'public';
        }
        if ($that->isProtected()) {
            return 'protected';
        }
        if ($that->isPrivate()) {
            return 'private';
        }
    }
}