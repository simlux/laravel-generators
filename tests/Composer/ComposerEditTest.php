<?php

namespace Simlux\LaravelGenerators\Tests\Composer;

use PHPUnit\Framework\TestCase;
use Simlux\LaravelGenerators\Composer\ComposerEdit;

class ComposerEditTest extends TestCase
{
    public function test()
    {
        $path = __DIR__ . '/../fixtures/';

        $edit = new ComposerEdit($path . 'composer_test.json');
        $edit->addProvider('SomeProvider');
        $edit->addAlias('Simlux', 'Simlux/SomeClass');
        $edit->addDontDiscover('AnotherClass');

        $testFile = $path . 'composer_produced.json';
        $edit->write($testFile);

        $this->assertSame(file_get_contents($path . 'composer_expected.json'), file_get_contents($testFile));
        unlink($testFile);
    }
}