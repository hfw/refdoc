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
        return [256 => 'public', 512 => 'protected', 1024 => 'private'][$that->getModifiers() & 1792];
    }
}