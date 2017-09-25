<?php

namespace Simlux\LaravelGenerators\Tests;

use PHPUnit\Framework\TestCase as BaseTest;

abstract class TestCase extends BaseTest
{
    /**
     * @param string|null $file
     *
     * @return string
     */
    protected function fixturePath(string $file = null): string
    {
        $fixture = __DIR__ . '/fixtures/';

        if (!is_null($file)) {
            $fixture .= $file;
        }

        return $fixture;
    }
}