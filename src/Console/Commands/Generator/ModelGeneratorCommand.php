<?php

namespace Simlux\LaravelGenerators\Console\Commands\Generator;

use Illuminate\Console\Command;
use Simlux\LaravelGenerators\Database\InformationSchema;
use Simlux\LaravelGenerators\Generators\ModelGenerator;
use Simlux\String\StringBuffer;

class ModelGeneratorCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'model:generate:database {--model=} {--table=}';

    /**
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $model;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleOptions();

        $generator         = new ModelGenerator($this->model, $this->table);
        $informationSchema = new InformationSchema();

        $this->info(sprintf('Generating model from table %s', $this->table));

        $informationSchema->getTableStructure($this->table)->each(function ($column) use ($generator) {
            $type = $this->mysqlTypeToPhpType($column->type, $column->column_type);
            switch ($type) {

                case 'Carbon':
                    $generator->dateColumn($column->name);
                    $generator->use('Carbon\Carbon');
                    break;

                case 'bool':
                    $generator->casts($column->name, 'boolean');
                    break;

                case 'float':
                    $generator->casts($column->name, 'float');
                    break;

            }

            $generator->addColumn($column->name, $type);
        });

        $generator->generate();
    }

    function handleOptions()
    {
        $this->table = $this->option('table');
        if ($this->option('model')) {
            $this->model = $this->option('model');
        } else {
            $this->model = $this->tableToModel($table);
        }
    }

    /**
     * @param string $table
     *
     * @return string
     */
    private function tableToModel(string $table): string
    {
        $buffer = new StringBuffer($table);

        $words = collect($buffer->split('_'))->map(function ($item) {
            return StringBuffer::create($item)
                ->ucFirst()
                ->toString();
        });

        $buffer = new StringBuffer($words->implode(''));

        if ($buffer->endsWith('ies', true)) {
            $buffer->substring(0, -3);
            $buffer->append('y');
        } else {
            if ($buffer->endsWith('s')) {
                $buffer->substring(0, -1);
            }
        }

        return $buffer->toString();
    }

    /**
     * @param string $type
     * @param string $columnType
     *
     * @return string
     */
    private function mysqlTypeToPhpType(string $type, string $columnType = null): string
    {
        switch ($type) {

            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'mediumtext':
            case 'text':
                $type = 'string';
                break;

            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                if ($type === 'tinyint' && strpos($columnType, 'tinyint(1)') !== false) {
                    $type = 'bool';
                } else {
                    $type = 'int';
                }
                break;

            case 'float':
            case 'decimal':
                $type = 'float';
                break;

            case 'date':
            case 'datetime':
            case 'timestamp':
                $type = 'Carbon';
                break;

            case 'boolean':
                $type = 'bool';
                break;
        }

        return $type;
    }
}
