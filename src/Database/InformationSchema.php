<?php declare(strict_types=1);

namespace Simlux\LaravelGenerators\Database;

use Config;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class InformationSchema
 *
 * @package InspectYourWeb\Database
 */
class InformationSchema
{
    private const TABLE_TABLES      = 'TABLES';
    private const TABLE_COLUMNS     = 'COLUMNS';
    private const COLUMN_TABLE_NAME = 'TABLE_NAME';
    private const COLUMN_SCHEMA     = 'TABLE_SCHEMA';

    /**
     * @var string
     */
    private $connection = 'information';

    /**
     * InformationSchema constructor.
     *
     * @param string|null $connection
     */
    public function __construct(string $connection = null)
    {
        if (!is_null($connection)) {
            $this->connection = $connection;
        }
    }

    /**
     * @param string|null $database
     *
     * @return Builder
     */
    private function getTableQuery(string $database = null): Builder
    {
        return DB::connection($this->connection)
            ->table(self::TABLE_TABLES)
            ->where(self::COLUMN_SCHEMA, $this->getStandardDatabaseIfNull($database))
            ->orderBy(self::COLUMN_TABLE_NAME, 'asc');
    }

    /**
     * @param string|null $database
     *
     * @return Collection
     */
    public function getTablesWithInformation(string $database = null): Collection
    {
        return $this->getTableQuery($database)
            ->select([
                'TABLE_NAME AS name',
                'ENGINE AS engine',
                'ROW_FORMAT AS format',
                'TABLE_ROWS AS rows',
                'TABLE_COLLATION AS collation',
                'AUTO_INCREMENT AS auto_increment',
                'DATA_LENGTH AS data_length',
                'INDEX_LENGTH AS index_length',
            ])
            ->get();
    }

    /**
     * @param string|null $database
     *
     * @return Collection
     */
    public function getTableNames(string $database = null): Collection
    {
        return $this->getTableQuery($database)
            ->pluck(self::COLUMN_TABLE_NAME);
    }

    /**
     * @param string      $table
     * @param string|null $database
     *
     * @return Collection
     */
    public function getTableStructure(string $table, string $database = null): Collection
    {
        return DB::connection($this->connection)
            ->table(self::TABLE_COLUMNS)
            ->select([
                'COLUMN_NAME AS name',
                'DATA_TYPE AS type',
                'CHARACTER_MAXIMUM_LENGTH AS character_length',
                'NUMERIC_PRECISION AS numeric_precision',
                'NUMERIC_SCALE AS numeric_scale',
                'CHARACTER_SET_NAME AS charset',
                'COLLATION_NAME AS collation',
                'COLUMN_COMMENT AS comment',
                'COLUMN_KEY AS key',
                'COLUMN_TYPE AS column_type',
            ])
            ->selectRaw('IF(IS_NULLABLE = "NO", 0, 1) as nullable')
            ->selectRaw('IF(EXTRA = "auto_increment", 1, 0) as auto_increment')
            ->where(self::COLUMN_SCHEMA, $this->getStandardDatabaseIfNull($database))
            ->where(self::COLUMN_TABLE_NAME, $table)
            ->orderBy('ORDINAL_POSITION', 'asc')
            ->get();
    }

    /**
     * @param string      $table
     * @param string|null $database
     *
     * @return Collection
     */
    public function getForeignKeys(string $table, string $database = null): Collection
    {
        return DB::connection($this->connection)
            ->table('KEY_COLUMN_USAGE')
            ->select([
                'CONSTRAINT_NAME AS name',
                'COLUMN_NAME AS column',
                'REFERENCED_TABLE_SCHEMA AS ref_database',
                'REFERENCED_TABLE_NAME AS ref_table',
                'REFERENCED_COLUMN_NAME AS ref_column',
            ])
            ->where(self::COLUMN_SCHEMA, $this->getStandardDatabaseIfNull($database))
            ->where(self::COLUMN_TABLE_NAME, $table)
            ->orderBy('COLUMN_NAME', 'asc')
            ->get();
    }

    /**
     * @param string|null $database
     *
     * @return string
     */
    private function getStandardDatabaseIfNull(string $database = null): string
    {
        if (is_null($database)) {
            return Config::get(sprintf('database.connections.%s.database', Config::get('database.default')));
        }

        return $database;
    }

    /**
     * @param string $connection
     */
    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }
}