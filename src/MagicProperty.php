<?php

namespace Helix\RefDoc;

/**
 * A magic property.
 */
class MagicProperty extends AbstractMagicMember {

    /**
     * @var MagicType
     */
    protected $type;

    /**
     * @param AbstractRefClass $owner
     * @param string $docblock
     * @return static[]
     */
    public static function fromDocBlock (AbstractRefClass $owner, string &$docblock) {
        static $rx = <<<'RX'
        /^
            @property\h+
            ((?<T>\w\S+)\h+)?   # must start with a word character. can have unions, [], etc.
            \$(?<N>\w+)\h*      # must start with $ followed by word characters.
            (?<C>((?!           # read until:
                \n@             # next tag
                |
                \n\n            # or empty comment line
            ).)*)               # or end of docblock
        /imsx
        RX;
        $props = [];
        while (preg_match($rx, $docblock, $p)) {
            $log = ~LOG_USER;
            $prop = new static;
            $prop->name = $p['N'];
            $prop->fqn = "{$owner}::\${$prop->name}";
            if (!$type = $p['T']) {
                Log::verboseMember('?', "@property \${$prop->name} assuming mixed type", ~LOG_WARNING);
                $prop->warnings[] = "This has no documented type. Assumed <code>mixed</code>.";
                $type = 'mixed';
                $prop->logLevel = $log = LOG_WARNING;
            }
            $prop->type = new MagicType($type);
            Log::verboseMember('+', "@property \${$prop->name} = {$prop->type}", $log);
            $prop->setDocBlock($p['C']);
            $props[] = $prop;
            $docblock = str_replace($p[0], '', $docblock);
        }
        while (preg_match('/^@property.*$/m', $docblock, $p)) {
            Log::member('X', "{$p[0]} couldn't be parsed", LOG_ERR);
            $docblock = str_replace($p[0], '', $docblock);
        }
        return $props;
    }

    /**
     * @return MagicType
     */
    public function getType () {
        return $this->type;
    }

    /**
     * Magic properties are never `static`, they can only exist for instances.
     *
     * @return bool
     */
    final public function isStatic (): bool {
        return 'false';
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::member('$', $this->name, $this->logLevel);
        $xml->startElement('magicProperty', Log::verbose([
            'fqn' => $this->fqn,
            'name' => $this->name,
            'visibility' => 'public'
        ]));
        $this->type->write($xml);
        $this->writeDocBlock($xml, $this->docblock);
        $xml->endElement();
    }
}