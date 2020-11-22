<?php

namespace Helix\RefDoc;

/**
 * Static logger.
 */
class Log {

    /**
     * ANSI escape colors.
     *
     * @var array
     */
    protected static $colors = [
        // reset
        0 => "\e[0m",
        // bright colors
        'entity' => "\e[97m",
        LOG_DEBUG => "\e[90m",          // "bright black" (default)
        LOG_ERR => "\e[91m",            // bright red
        LOG_USER => "\e[92m",           // bright green (magic members)
        LOG_WARNING => "\e[93m",        // bright yellow ("review this")
        LOG_INFO => "\e[94m",           // blue (members default)
        // dark colors
        ~LOG_DEBUG => "\e[30m",     // near-black (verbose default)
        -1 => "\e[30m",             // alias for ~LOG_DEBUG
        ~LOG_ERR => "\e[31m",       // dark red
        ~LOG_USER => "\e[32m",      // dark green
        ~LOG_WARNING => "\e[33m",   // dark yellow
        ~LOG_INFO => "\e[34m",      // dark blue
    ];

    public static $verbose = false;

    /**
     * Outputs a debug line to `STDERR`
     *
     * @param string $text
     */
    public static function debug (string $text): void {
        self::line(self::$colors[LOG_DEBUG] . $text . self::$colors[0]);
    }

    public static function disableColors (): void {
        foreach (array_keys(static::$colors) as $key) {
            static::$colors[$key] = '';
        }
    }

    /**
     * Outputs an entity name to `STDERR`
     *
     * @param string $entity
     */
    public static function entity (string $entity): void {
        self::debug(self::$colors['entity'] . $entity);
    }

    /**
     * Outputs an error to `STDERR`
     *
     * @param string $error
     */
    public static function error (string $error): void {
        self::line("\n" . self::$colors[LOG_ERR] . $error . self::$colors[0]);
    }

    /**
     * Outputs a raw line to `STDERR` without decoration.
     *
     * @param string $text
     */
    public static function line (string $text = ''): void {
        fputs(STDERR, $text . "\n");
    }

    /**
     * Outputs a line about entity member/s to `STDERR`
     *
     * Color will apply to the whole line if `$level` is negative / more severe than `LOG_INFO`.
     *
     * @param string $code
     * @param string|string[] $member
     * @param int $level
     */
    public static function member (string $code, $member, int $level = LOG_INFO): void {
        $members = is_array($member) ? $member : [$member];
        foreach ($members as $member) {
            self::debug(sprintf('    [%s] %s%s',
                self::$colors[$level] . $code . self::$colors[LOG_DEBUG],
                $level <= LOG_WARNING ? self::$colors[$level] : self::$colors[0],
                $member
            ));
        }
    }

    /**
     * Exports a variable to `STDERR` and returns it back for fluency.
     *
     * @param mixed $mixed
     * @return mixed
     */
    public static function var ($mixed) {
        self::line(Util::toExport($mixed));
        return $mixed;
    }

    /**
     * Outputs an indented verbose line to `STDERR`
     *
     * Always ensure {@link XMLWriterInterface::write()} only has one possible
     * trailing line when applicable.
     *
     * @param string|string[] $text Line/s
     * @param int $level
     * @return mixed The lines given.
     */
    public static function verbose ($text = '', int $level = -1) {
        static $indent = '        ';
        if (isset($text) and static::$verbose) {
            if (is_string($text)) {
                self::line($indent . self::$colors[$level] . $text . self::$colors[0]);
                return $text;
            }
            foreach (array_filter($text) as $k => $line) {
                $line = is_string($k) ? sprintf("%-12s%s", $k . ':', $line) : $line;
                self::line($indent . self::$colors[$level] . $line . self::$colors[0]);
            }
        }
        return $text;
    }

    /**
     * {@see member()} with an added check on the verbosity flag.
     *
     * @param string $code
     * @param string|string[] $member
     * @param int $level
     */
    public static function verboseMember (string $code, $member, int $level = -1): void {
        if (self::$verbose) {
            self::member($code, $member, $level);
        }
    }

}