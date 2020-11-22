<?php

interface I {

    const X = 1;

    function foo ();
}

/**
 * Traits cannot have constants.
 */
trait T {

    /**
     * T->foo
     */
    public $foo; // this is valid. ide bug report submitted.

    function baz () {

    }
}

trait T2 {

    /**
     * T2->foo
     */
    public $foo;

}

class X implements I {

    // const X = 1; constants may not conflict ever.
    // X::X->class is X, despite being from I

    const Y = 1;

    public static $bar;

    public $foo;

    function bar () {
    }

    function foo () {
    }
}

/**
 * Y->getConstant(Y)->class === Y
 * Y->getProperty(foo)->class === X
 * Y->getProperty(bar)->class === X
 * Y->hasMethod(foo) === true
 * Y->getMethod(foo)->class === X
 * Y->getMethod(bar)->class === Y
 */
class Y extends X {

    function bar () {
    }
}

/**
 * Z->getMethod(baz)->class === Z (traits are copy-paste and not classes)
 */
class Z extends Y {

    use T, T2; // T->foo docblock is used.
}

$r = new ReflectionClass(Y::class);
var_dump($r->getProperty('bar')->class);