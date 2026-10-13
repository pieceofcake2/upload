<?php
/**
 * All Upload plugin tests
 */

class AllTestsTest extends CakeTestCase
{
    /**
     * Suite define the tests for this suite
     *
     * @return CakeTestSuite
     */
    public static function suite(): CakeTestSuite
    {
        $suite = new CakeTestSuite('All Upload test');

        $path = CakePlugin::path('Upload') . 'tests' . DS . 'TestCase' . DS;
        $suite->addTestDirectoryRecursive($path);

        return $suite;
    }
}
