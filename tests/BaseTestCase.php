<?php

namespace Tests;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    /**
     * Asserts that two commands are equal.
     *
     * The comparison is made after removing all quotes as a simple workaround for
     * Symfony Process having different quoting behavior on Windows vs. Linux
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public static function assertCommandEquals($expected, $actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        parent::assertEquals(str_replace(['\'', '"'], '', $expected), str_replace(['\'', '"'], '', $actual), $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }
}