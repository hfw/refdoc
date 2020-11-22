<?php

namespace Helix\RefDoc;

use DOMDocument;
use XMLWriter;

/**
 * Reflects to XML
 */
class RefXML extends XMLWriter {

    /**
     * Only output root entities matching these names or namespaces.
     *
     * Default: all namespaces
     *
     * @var array
     */
    protected $classes = [];

    /**
     * Excludes source files matching these strings.
     *
     * @var string[]
     */
    protected $exclude = [];

    /**
     * Source files and directories for inclusion.
     *
     * @var array
     */
    protected $sources = ['src'];

    /**
     * @return string
     */
    public function __toString () {
        return $this->outputMemory();
    }

    protected function _includeAll (array $paths): void {
        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->_includeFile($path);
            }
            else {
                $this->_includeAll(glob("{$path}/*.php"));
                $this->_includeAll(glob("{$path}/*", GLOB_ONLYDIR));
            }
        }
    }

    protected function _includeFile (string $file): void {
        foreach ($this->exclude as $str) {
            if (false !== strpos($file, $str)) {
                Log::member('-', $file, -1);
                return;
            }
        }
        Log::member('+', $file, LOG_USER);
        include_once "{$file}";
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function filterNamespace (string $name): bool {
        if (!$this->classes) {
            return true;
        }
        foreach ($this->classes as $ns) {
            if (strpos($name, $ns) === 0) {
                return true;
            }
        }
        return false;
    }

    public function generate (): void {
        Log::entity("\nLoading Sources...");
        $this->_includeAll($this->sources);
        Log::entity("\nProcessing Entities...\n");
        $this->openMemory();
        $this->setIndent(true);
        $this->setIndentString('  ');
        $this->startDocument('1.0', 'UTF-8');
        $this->startElement('entities');
        $this->writeAttribute('generated', date(DATE_W3C));
        if ($interfaces = $this->getInterfaces()) {
            $this->startElement('interfaces');
            foreach ($interfaces as $interface) {
                (new RefInterface($interface))->write($this);
            }
            $this->endElement();
        }
        if ($traits = $this->getTraits()) {
            $this->startElement('traits');
            foreach ($traits as $trait) {
                (new RefTrait($trait))->write($this);
            }
            $this->endElement();
        }
        if ($classes = $this->getClasses()) {
            $this->startElement('classes');
            foreach ($classes as $class) {
                (new RefClass($class))->write($this);
            }
            $this->endElement();
        }
        $this->endElement();
        $this->endDocument();
    }

    /**
     * @return string[]
     */
    public function getClasses () {
        return array_filter(get_declared_classes(), [$this, 'filterNamespace']);
    }

    /**
     * @return string[]
     */
    public function getExclude (): array {
        return $this->exclude;
    }

    /**
     * @return string[]
     */
    public function getInterfaces () {
        return array_filter(get_declared_interfaces(), [$this, 'filterNamespace']);
    }

    /**
     * @return string[]
     */
    public function getSources (): array {
        return $this->sources;
    }

    /**
     * @return string[]
     */
    public function getTraits () {
        return array_filter(get_declared_traits(), [$this, 'filterNamespace']);
    }

    /**
     * @param null|string $classList
     * @return $this
     */
    public function setClasses (?string $classList) {
        if ($classList) {
            $this->classes = array_map('trim', explode(',', str_replace('/', '\\', $classList)));
        }
        return $this;
    }

    /**
     * @param null|string $excludePatternList
     * @return $this
     */
    public function setExclude (?string $excludePatternList) {
        if ($excludePatternList) {
            $this->exclude = array_map('trim', explode(',', $excludePatternList));
        }
        return $this;
    }

    /**
     * @param null|string $sourceList
     * @return $this
     */
    public function setSources (?string $sourceList) {
        if ($sourceList) {
            $this->sources = array_map('trim', explode(',', $sourceList));
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string[] $attrs
     * @return bool|void
     */
    public function startElement ($name, array $attrs = []) {
        parent::startElement($name);
        $this->writeAttributes($attrs);
    }

    /**
     * @return DOMDocument
     */
    public function toDOMDocument () {
        $dom = new DOMDocument;
        $dom->loadXML($this);
        return $dom;
    }

    /**
     * Writes multiple attributes, ignores empty strings.
     *
     * @param string[] $attrs
     */
    public function writeAttributes (array $attrs): void {
        foreach ($attrs as $name => $value) {
            if (is_int($name)) {
                $name = $value;
                $value = 1;
            }
            if ($name and $value) {
                $this->writeAttribute($name, $value);
            }
        }
    }

    /**
     * Can write an element with an array of attributes.
     *
     * Will not write empty elements. Use attributes for that.
     *
     * @param string $name
     * @param string|XMLWriterInterface $content
     * @param string[] $attrs
     * @param bool $cdata
     * @return bool
     */
    public function writeElement ($name, $content = null, array $attrs = [], $cdata = false) {
        if (!strlen($content) and !$attrs) {
            return true;
        }
        $this->startElement($name, $attrs);
        if (isset($content)) {
            if ($content instanceof XMLWriterInterface) {
                $content->write($this);
            }
            elseif ($cdata) {
                $this->writeCdata($content);
            }
            else {
                $this->text($content);
            }
        }
        return $this->endElement();
    }

    /**
     * Writes repeating elements.
     *
     * @param string $listName List container element name.
     * @param null|string $itemName Repeated list item name. This is ignored when `$items` can write themselves.
     * @param string[]|XMLWriterInterface[] $items List item contents.
     * @param string[] $listAttrs Attributes for `$listName`
     * @param bool $cdata Whether list items are CData.
     */
    public function writeList (
        string $listName,
        ?string $itemName,
        array $items,
        array $listAttrs = [],
        bool $cdata = false
    ): void {
        if (!$items and !$listAttrs) {
            return;
        }
        $this->startElement($listName);
        $this->writeAttributes($listAttrs);
        foreach ($items as $key => $item) {
            if ($item instanceof XMLWriterInterface) {
                $item->write($this);
            }
            else {
                $this->writeElement($itemName ?? $key, $item, [], $cdata);
            }
        }
        $this->endElement();
    }
}