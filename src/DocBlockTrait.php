<?php

namespace Helix\RefDoc;

use Parsedown;

trait DocBlockTrait {

    /**
     * @var string[]
     */
    protected $warnings = [];

    /**
     * Writes the docblock.
     *
     * Instead of pulling the docblock from `$this`, a working-copy is given.
     *
     * This gives the reflector a chance to modify the docblock before it's committed.
     *
     * The docblock is cleaned of inconsequential leading whitespace (before `*`) on each line,
     * and is ultimately ignored if empty.
     *
     * @param RefXML $xml
     * @param string $docblock
     * @todo resolve aliases to fqn
     */
    protected function writeDocBlock (RefXML $xml, string $docblock): void {
        // strip flower box
        $docblock = preg_replace('/^\/\*+\s*/', '', $docblock);
        $docblock = preg_replace('/^\h*\*\h?/m', '', $docblock);
        $docblock = preg_replace('/\s*\**\/$/', '', $docblock);

        // auto-inline external links
        $docblock = preg_replace('/{@(see|link)\h+(\w+:\/\/(\S+))\h*}/i', '[$3]($2)', $docblock);
        $docblock = preg_replace('/{@(see|link)\h+(\w+:\/\/\S+)\h+([^}]+)}/i', '[$3]($2)', $docblock);

        // auto-inline links to other entities/members
        // fixme: need to resolve aliasing to fqn
        $docblock = preg_replace('/{@(see|link)\h+(\S+)\h*}/i', '[$2](#$2)', $docblock);
        $docblock = preg_replace('/{@(see|link)\h+(\S+)\h+([^}]+)}/i', '[$3](#$2)', $docblock);

        // prune tags
        $this->writeDocBlock_Prune($docblock);

        // write tag list
        if (preg_match('/^@/m', $docblock)) {
            $this->writeDocBlock_Tags($xml, $docblock);
            $badTags = [];
            while (preg_match('/^(@.*)$/m', $docblock, $badTag)) {
                Log::verbose($badTag[1], ~LOG_WARNING);
                $badTags[] = $badTag[1];
                $docblock = str_replace($badTag[0], '', $docblock);
            }
            $xml->writeList('badTags', 'tag', $badTags);
        }

        $xml->writeList('warnings', 'warning', $this->warnings, [], true);

        if (strlen($docblock = trim($docblock))) {
            $xml->writeElement('docblock', (new Parsedown)->text($docblock), [], true);
        }
    }

    /**
     * @param $docblock
     */
    protected function writeDocBlock_Prune (&$docblock): void {
        $docblock = preg_replace('/^@return\h+.*$/m', '', $docblock);
        $docblock = preg_replace('/^@var\h+.*$/m', '', $docblock);
    }

    /**
     * @param RefXML $xml
     * @param string $docblock
     */
    protected function writeDocBlock_Tags (RefXML $xml, string &$docblock): void {
        $this->writeDocBlock_Tags_internal($xml, $docblock);
        $this->writeDocBlock_Tags_see($xml, $docblock);
        $this->writeDocBlock_Tags_todo($xml, $docblock);
    }

    /**
     * @param RefXML $xml
     * @param string $docblock
     */
    protected function writeDocBlock_Tags_internal (RefXML $xml, string &$docblock): void {
        if (preg_match('/^@internal(\h+(?<comment>.+))?$/im', $docblock, $tag)) {
            $xml->writeElement('internal', $tag['comment'] ?? ' ');
            $docblock = str_replace($tag[0], '', $docblock);
        }
    }

    /**
     * @param RefXML $xml
     * @param string $docblock
     * @todo resolve aliases to fqn
     */
    protected function writeDocBlock_Tags_see (RefXML $xml, string &$docblock): void {
        $links = [];
        while (preg_match('/^@(see|link)\h+(?<L>\S+)(\h+(?<C>.+))?$/im', $docblock, $tag)) {
            $text = $tag['C'] ?? $tag['L'];
            $location = $tag['L'];
            if (!preg_match('/^\w+:\/\//', $location)) {
                $location = "#{$location}";
            }
            $links[] = "- [{$text}]({$location})";
            $docblock = str_replace($tag[0], '', $docblock);
        }
        if ($links) {
            $xml->writeElement('links', (new Parsedown)->text(implode("\n", $links)), [], true);
        }
    }

    /**
     * @param RefXML $xml
     * @param string $docblock
     */
    protected function writeDocBlock_Tags_todo (RefXML $xml, string &$docblock): void {
        $todo = [];
        // to-do with description
        while (preg_match('/^@todo\h+(.+)$/im', $docblock, $tag)) {
            $todo[] = "- {$tag[1]}";
            $docblock = str_replace($tag[0], '', $docblock);
        }
        // lonely to-do
        if (preg_match('/^@todo\h*$/im', $docblock, $tag)) {
            $todo[] = '<!-- empty todo -->';
            $docblock = str_replace($tag[0], '', $docblock);
        }
        if ($todo) {
            $xml->writeElement('todo', (new Parsedown)->text(implode("\n", $todo)), [], true);
        }
    }
}