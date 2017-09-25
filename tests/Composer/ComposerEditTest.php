<?php

namespace Simlux\LaravelGenerators\Tests\Composer;

use Simlux\LaravelGenerators\Tests\TestCase;
use Simlux\LaravelGenerators\Composer\ComposerEdit;

class ComposerEditTest extends TestCase
{
    public function testExtendComposerJsonFile()
    {
        $edit = new ComposerEdit($this->fixturePath('composer_test.json'));
        $edit->addProvider('SomeProvider');
        $edit->addAlias('Simlux', 'Simlux/SomeClass');
        $edit->addDontDiscover('AnotherClass');

        $testFile = $this->fixturePath('composer_produced.json');
        $edit->write($testFile);

        $this->assertSame(file_get_contents($this->fixturePath('composer_expected.json')), file_get_contents($testFile));
        unlink($testFile);
    }
}