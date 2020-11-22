<?php

namespace Helix\RefDoc;

use ParseError;

class MagicParam implements XMLWriterInterface {

    /**
     * @var string
     */
    public $def = '';

    /**
     * @var null|string
     */
    public $default;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var bool
     */
    public $reference = false;

    /**
     * @var MagicType
     */
    public $type;

    /**
     * @param string $params
     * @return static[]
     */
    public static function fromList (string $params) {
        $magicParams = [];
        $params = Util::toShortArray($params);
        // split param list on comma followed by "?\type[]|... &$name"
        $params = preg_split('/,\h*(?=([\\\\\w\[\]\|?]+\h+)?&?\$\w+)/', $params);
        foreach ($params as $param) {
            $magicParams[] = new static($param);
        }
        return $magicParams;
    }

    /**
     * @param string $param
     */
    public function __construct (string $param) {
        $this->def = $param = trim($param);
        $rx = <<<'RX'
        /^
            ((?<T>[\w\[\]\|\\\\?]+)\h+)?
            (?<R>&?)\$(?<N>\w+)
            (\h*=\h*(?<D>.+))?
        $/ix
        RX;
        preg_match($rx, $param, $p);
        $this->type = new MagicType($p['T'] ?: 'mixed');
        $this->name = $p['N'];
        if (isset($p['D'])) {
            if (substr_count($p['D'], '(') !== substr_count($p['D'], ')')) {
                throw new ParseError("\${$this->name} has unmatched parenthesis: {$p['D']}");
            }
        }
        $this->default = $p['D'] ?? null;
        $this->reference = (bool)$p['R'];
    }

    public function __toString () {
        return $this->def;
    }

    /**
     * @return bool
     */
    public function allowsNull (): bool {
        return $this->type->allowsNull() or strtolower($this->default === 'null');
    }

    /**
     * @param RefXML $xml
     */
    public function write (RefXML $xml): void {
        Log::verbose('param', LOG_DEBUG);
        $xml->startElement('param', Log::verbose([
            'name' => $this->name,
            $this->allowsNull() ? 'nullable' : null,
            $this->reference ? 'reference' : null
        ]));
        $this->type->write($xml);
        $xml->writeElement('default', $this->default, [], true);
        $xml->endElement();
        Log::verbose();
    }
}