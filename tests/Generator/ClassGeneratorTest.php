<?php declare(strict_types=1);

namespace InspectYourWeb\Tests\Generator;

use Simlux\LaravelGenerators\Generators\ClassGenerator;
use Tests\TestCase;

class ClassGeneratorTest extends TestCase
{
    public function test()
    {
        $classFile = base_path('tests/tmp/Project.php');

        $generator = new ClassGenerator($classFile, 'class', 'generator', base_path('tests/Unit/fixtures/views/generator'));
        $generator->addTemplateVar('className', 'Project');

        $generator->strictTypes(true);
        $generator->namespace('InspectYourWeb\Models');
        $generator->extends('AbstractModel');
        $generator->use('Carbon\Carbon');
        $generator->use('Simlux\LaravelModelUuid\Uuid\UuidModelTrait');
        $generator->trait('UuidModelTrait');

        $generator->addProperty('id', 'int');
        $generator->addProperty('uuid', 'string');
        $generator->addProperty('created_at', 'Carbon');
        $generator->addProperty('updated_at', 'Carbon');

        $generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'table', 'string', 'projects');
        $generator->addVar(ClassGenerator::VISIBILITY_PUBLIC, 'timestamps', 'bool', true);
        $generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'fillable', 'array', []);
        $generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'guarded', 'array', ['*']);
        $generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'dates', 'array', ['created_at', 'updated_at']);
        $generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'casts', 'array', ['domain_owner' => 'boolean', 'prefer_https' => 'boolean']);

        $generator->generate();

        $this->assertTrue(file_exists($classFile));

        #unlink($classFile);
    }
}