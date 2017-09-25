<?php declare(strict_types=1);

namespace Simlux\LaravelGenerators\Generators;

class ModelGenerator
{
    /**
     * @var ClassGenerator
     */
    private $generator;

    /**
     * @var string
     */
    private $modelName;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var bool
     */
    private $timestamps = false;

    /**
     * @var array
     */
    private $dateColumns = [];

    /**
     * @var array
     */
    private $columns = [];

    /**
     * @var array
     */
    private $casts = [];

    /**
     * ModelGenerator constructor.
     *
     * @param string $modelName
     * @param string $tableName
     */
    public function __construct(string $modelName, string $tableName)
    {
        $this->modelName = $modelName;
        $this->tableName = $tableName;

        $this->generator = new ClassGenerator(
            app_path(sprintf('Models/%s.php', $modelName)),
            'class',
            'generator',
            resource_path('generator/views')
        );
        $this->generator->addTemplateVar('className', $modelName);
        $this->setVars();
    }

    private function prepare()
    {
        $this->setClassBasics();
        $this->setProperties();
        $this->setVars();
    }

    public function generate()
    {
        $this->prepare();
        $this->generator->generate();
    }

    private function setClassBasics()
    {
        $this->generator->strictTypes(true);
        $this->generator->namespace('InspectYourWeb\Models');
        $this->generator->extends('AbstractModel');
    }

    private function setVars()
    {
        $this->generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'table', ClassGenerator::TYPE_STRING, $this->tableName);
        $this->generator->addVar(ClassGenerator::VISIBILITY_PUBLIC, 'timestamps', ClassGenerator::TYPE_BOOL, $this->timestamps);
        $this->generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'fillable', ClassGenerator::TYPE_ARRAY, "[]");
        $this->generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'guarded', ClassGenerator::TYPE_ARRAY, "['*']");
        $this->generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'dates', ClassGenerator::TYPE_ARRAY, $this->dateColumns);
        $this->generator->addVar(ClassGenerator::VISIBILITY_PROTECTED, 'casts', ClassGenerator::TYPE_ARRAY, $this->casts);
    }

    private function setProperties()
    {
        collect($this->columns)->each(function ($type, $column) {
            $this->generator->addProperty($column, $type);
        });
    }

    public function timestamps()
    {
        $this->timestamps = true;
        $this->dateColumn('created_at');
        $this->dateColumn('updated_at');
    }

    /**
     * @param string $column
     */
    public function dateColumn(string $column)
    {
        if (!in_array($column, $this->dateColumns)) {
            $this->dateColumns[] = $column;
        }
    }

    /**
     * @param string $name
     * @param string $type
     */
    public function addColumn(string $name, string $type)
    {
        $this->columns[ $name ] = $type;
    }

    /**
     * @param string $class
     */
    public function use (string $class)
    {
        $this->generator->use($class);
    }

    /**
     * @param string $column
     * @param string $type
     */
    public function casts(string $column, string $type)
    {
        if (!isset($this->casts[$column])) {
            $this->casts[$column] = $type;
        }
    }

}