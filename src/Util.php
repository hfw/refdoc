<?php

namespace Helix\RefDoc;

/**
 * Helper functions.
 */
class Util {

    /**
     * A nicer `var_export()`
     *
     * @param mixed $mixed
     * @return string
     */
    public static function toExport ($mixed): string {
        // guard against static objects set during autoload.
        if (is_object($mixed)) {
            return get_class($mixed);
        }
        $export = var_export($mixed, true);
        if (is_array($mixed)) {
            $export = static::toShortArray($export);
            if (substr_count($export, ',') < 10) {
                $export = str_replace("\n", ' ', $export);
            }
        }
        $export = preg_replace('/[^ -~]/', '?', $export);
        return $export;
    }

    /**
     * Converts all dimensions of `array(...)` to `[...]`.
     *
     * @param string $string
     * @return string
     */
    public static function toShortArray (string $string): string {
        do {
            $string = preg_replace('/array\h*\((.*?)\)/is', '[$1]', $string, -1, $count);
        } while ($count);
        return $string;
    }

    /**
     * Converts PHP `gettype()` string to doc-friendly string.
     *
     * @param string $type
     * @return string
     */
    public static function toShortType (string $type): string {
        return ['integer' => 'int', 'boolean' => 'bool', 'double' => 'float'][$type] ?? $type;
    }

}