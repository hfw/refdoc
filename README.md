hfw/refdoc
==========
PHP Documentation via Reflection

This project is in a prototyping phase. The name and repository location may change.

Open for testing!

```
$ composer --dev require "hfw/refdoc@dev"

$ ./vendor/bin/refdoc.php --help
```

About
-----

This library leverages reflection and other core functionality
to generate documentation for PHP code.

The goal is to be light weight, fast, robust, and flexible.

How it works is as follows:
- First we `include()` project source files.
- Then we reflect into an intermediate XML format, kept in memory.
- ~~Then we run some post-processing in order to generate embedded relationship graphs.~~ Coming soon.
- Finally, we transform the intermediate XML into HTML via XSLT.

The entire process is extremely quick, and can document the whole of PHP core in a manner of milliseconds.

TODO
----
- [ ] Prototype XML-to-Document
    - [x] Vanilla XSLT for HTML5 + Bootstrap
    - [ ] Graphs via `dot`
        - [ ] Different edge colors for each type of composition
    - [x] Inline everything to produce a SINGLE document
        - [ ] images
        - [x] css
    - [ ] Intermediate XML generation is sparse.
          Introduce an XSD to validate for sanity,
          and to provide a guide for creating layouts.
- [ ] Options:
    - [x] change source path/s
    - [x] filter by pattern
    - [ ] exclude core (yes)
    - [ ] exclude `private` (yes)
    - [ ] exclude `protected` (no)
    - [ ] exclude `@internal` (yes)
    - [ ] implied `@internal` for `__magic` methods (yes)
    - [x] change layout
    - [ ] `refdoc.json`