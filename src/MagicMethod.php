<?php

namespace Helix\RefDoc;

use ParseError;

/**
 * A magic method.
 */
class MagicMethod extends AbstractMagicMember {

    /**
     * @var MagicParam[]
     */
    public $params = [];

    /**
     * @var MagicType
     */
    public $return;

    /**
     * @var bool
     */
    public $static = false;

    /**
     * Param lists using the old `array()` syntax **MUST NOT HAVE** `)<space>`
     *
     * TODO split this out into fragment methods.
     *
     * @inheritDoc
     *
     * @param AbstractRefClass $owner
     * @param string $docblock
     * @return static[]
     */
    public static function fromDocBlock (AbstractRefClass $owner, string &$docblock) {
        static $rx = <<<'RX'
        /^
            @method\h+
            ((?<S>static)\h+)?  # match "static". may actually be the return type.
            ((?<T>(             # match a type or the method name.
                \$this | [      # only allow var return "this"
                    \\          # ns char
                    \w          # word char
                    \[\]        # array
                    \|          # union
                ]
            )+)\h+)?            # method may be typeless
            (?<N>\w+)
            \h*
            (?<P>
                \((?<PL>
                    ((?!\)\s).)*    # read until `)<space>`
                )\)
            )?              # method may be without param list
            \h*
            (?<C>((?!       # read until:
                    \n@     # next tag
                    |
                    \n\n    # or empty comment line
            ).)*)           # or end of docblock
        /imsxx
        RX;
        $methods = [];
        while (preg_match($rx, $docblock, $m)) {
            $log = ~LOG_USER;
            $method = new static;
            $method->name = $m['N'];
            $method->fqn = "{$owner}::{$method->name}()";
            $method->static = $m['S'] && $m['T'] && $m['N']; // all 3 are required
            if ($params = trim($m['PL'])) {
                try {
                    $method->params = MagicParam::fromList($m['PL']);
                }
                catch (ParseError $e) {
                    Log::member('X', "@method {$method->name} " . $e->getMessage(), LOG_ERR);
                    $docblock = str_replace($m[0], '', $docblock);
                    continue;
                }
            }
            elseif (!$m['P']) {
                Log::verboseMember('?', "@method {$method->name} assuming empty parameter list", ~LOG_WARNING);
                $method->warnings[] = "This has no declared parameter list. Assumed <code>()</code>.";
                $method->logLevel = $log = LOG_WARNING;
            }
            if (!$type = $m['T'] ?: ($m['S'] ?: null)) {
                Log::verboseMember('?', "@method {$method->name} () assuming mixed return type", ~LOG_WARNING);
                $method->warnings[] = "This has no documented return type. Assumed <code>mixed</code>.";
                $type = 'mixed';
                $method->logLevel = $log = LOG_WARNING;
            }
            $method->return = new MagicType($type);
            $sig = ($method->static ? 'static ' : '') . "{$method->name} ({$m['PL']}): {$method->return}";
            Log::verboseMember('+', '@method ' . $sig, $log);
            $method->setDocBlock($m['C']);
            $methods[] = $method;
            $docblock = str_replace($m[0], '', $docblock);
        }
        while (preg_match('/^@method.*$/m', $docblock, $m)) {
            Log::member('X', "{$m[0]} couldn't be parsed", LOG_ERR);
            $docblock = str_replace($m[0], '', $docblock);
        }
        return $methods;
    }

    /**
     * @return bool
     */
    final public function isStatic () {
        return $this->static;
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::member('>', "{$this->name}()", $this->logLevel);
        $xml->startElement('magicMethod', Log::verbose([
            'fqn' => $this->fqn,
            'name' => $this->name,
            'visibility' => 'public',
            $this->static ? 'static' : null,
            $this->return->allowsGenerator() ? 'generator' : null,
        ]));
        $xml->writeElement('return', $this->return);
        $xml->writeList('params', null, $this->params);
        $this->writeDocBlock($xml, $this->docblock);
        $xml->endElement();
        Log::verbose();
    }

}