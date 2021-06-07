<?php

/**
 * DO NOT FORMAT
 *
 * This code is deliberately trash in order to fuzz the docblock parser and generally test things.
 *
 * Please never let your code look like this.
 */
namespace Helix\RefDoc;

use stdClass;

/**
 * An interface.
 */
interface TestInterface {

    const TEST_INTERFACE_CONSTANT_UGLY_ARRAY = array ('$',' ', array('3'),);
}

interface TestInterface2 extends TestInterface {}

interface TestInterface3 extends TestInterface2 {}
/**
 * A trait.
 */
trait TestTrait1 {

    protected $testTrait1Protected;
}

/**
 * Another trait.
 */
trait TestTrait2 {

    use TestTrait1;
}

// empty docblock
/**
 */
abstract class TestAbstract implements TestInterface {

    private $testAbstractPrivate;

    /**
     * Generally, if you care, everything should be documented.
     *
     * If this abstract method has no docblock, and neither does the implementation,
     * the implementation will not be documented in its class.
     * (However, this will still be listed as an inherited method in either case).
     *
     * This may or may not be desirable in some situations,
     * but as long as the abstract has a docblock, and the implementer doesn't simply copy-paste it,
     * the implementation will documented and have this method listed as a parent.
     *
     * @return mixed
     */
    abstract public function implementMe();

    // This will be documented for this abstract class,
    // but the Test class implementation will not be documented there.
    abstract public function implementMeButDontDocument();

    /**
     * @var stdClass
     */
    protected $testAbstractProtectedStdClass;

    /**
     * A void method.
     */
    protected function testAbstractVoidMethod():void{}
}

// don't name test vars and params the same as core types. that's confusing. give them verbose names.
/**
 * Class comment.
 *
 * Magic Fuzz:
 * @property $warnMixedFallback Something.
 * @method \Generator getGenerator(?array &$nullableArrayRef = null) Returns a `\Generator`
 * @method int getConstant ($nullableTypeless = null, ?bool $myConst = STR_PAD_LEFT)
 * @property int $myInt An integer.
 * @method warnNakedMethod
 * @method warnMixedFallback(int $myInt, ?int[]|?\Helix\RefDoc\Test &$trashArrayRef = []) Returns something.
 * @property null|int[] $nullableIntArray
 * A comment for $nullableIntArray
 * @method $this|null getThisOrNull(string[]   $uglyStringArray = array ('$',' ', array('3'),), &$nullableTypelessRef=null)
 *  Returns `$this` or null.
 * @method static string getClass() @todo
 * A multiline
 *  comment for
//  getClass()
 *      With preserved indentation.
 *          - like so
 *          - and a lonely TODO
 * @method static getStatic() New `static`
 *
 * Invalids:
 * @property cantOmitSigil
 * @method $notThis cantReturnVarsOtherThanThis()
 * @method badDefaultValue( $badArray = array (1,2,3) , $y = null) param list contains ") ,"
 *
 * Class comment.
 *
 * Here is an inline `@see` to {@see TestAbstract},
 * and a descriptive inline `@link` to {@link TestAbstract test abstract}.
 *
 * Here is an inline `@link` to {@link https://php.net},
 * and a descriptive inline `@see` to {@see https://php.net php dot net}.
 *
 * @link https://getbootstrap.com
 * @see https://getbootstrap.com get bootstrap dot com
 * @todo a todo item
 * @todo another todo item
 */
class TestClass extends TestAbstract implements TestInterface {

    use TestTrait2;

    /**
     * @var SomeType
     */
    private $testVar;

    // this has the abstract method listed as a parent method,
    // because the docblocks differ. (there is no docblock here)
    public function implementMe () {}

    // this WON'T be documented in the class, because neither this or the abstract have docblocks for it.
    // it will still show up as an inherited method, though.
    // this may be desirable in some situations. in other situations, GIGO. document your code.
    public function implementMeButDontDocument () {}

    /**
     * My void method.
     */
    private static function testStaticVoid ($unusedRef = null): void {}

    /**
     * This is a very normal method.
     *
     * @param int $foo The first of two normal parameters.
     * @param string $bar The second, very normal parameter.
     * @return bool This is the return value's extremely normal comment.
     */
    public function aNormalMethod(int $foo, string $bar = 'a very normal default value'): bool {
        return true; // what an optimistic function this is
    }
}