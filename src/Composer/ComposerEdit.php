<?php declare(strict_types=1);

namespace Simlux\LaravelGenerators\Composer;

use Simlux\String\StringBuffer;

class ComposerEdit
{
    public const EXTRA_DONT_DISCOVER = 'dont-discover';
    public const EXTRA_PROVIDERS     = 'providers';
    public const EXTRA_ALIASES       = 'aliases';

    private const PSR4         = 'psr-4';
    private const EXTRA        = 'extra';
    private const LARAVEL      = 'laravel';
    private const AUTOLOAD     = 'autoload';
    private const AUTOLOAD_DEV = 'autoload_dev';

    /**
     * @var string
     */
    private $composerFile;

    /**
     * @var array
     */
    private $content;

    /**
     * @var array
     */
    private $standard = [
        self::EXTRA        => [
            self::LARAVEL => [
                self::EXTRA_DONT_DISCOVER => [],
                self::EXTRA_PROVIDERS     => [],
                self::EXTRA_ALIASES       => [],
            ],
        ],
        self::AUTOLOAD     => [
            self::PSR4 => [],
        ],
        self::AUTOLOAD_DEV => [
            self::PSR4 => [],
        ],
    ];

    /**
     * ComposerEdit constructor.
     *
     * @param string|null $composerFile
     *
     * @throws \Exception
     */
    public function __construct(string $composerFile = null)
    {
        if (!is_null($composerFile)) {
            if (!file_exists($composerFile)) {
                throw new \Exception($composerFile);
            }
            $this->composerFile = $composerFile;
        } else {
            $this->composerFile = __DIR__ . '/composer.json';
        }

        $this->content = json_decode(file_get_contents($this->composerFile), true);
        $this->content = array_merge_recursive($this->content, $this->standard);
    }

    /**
     * @param string $namespace
     * @param string $path
     * @param bool   $dev
     */
    public function addAutoload(string $namespace, string $path, bool $dev = false)
    {
        $key = $dev ? self::AUTOLOAD_DEV : self::AUTOLOAD;

        $namespace = new StringBuffer($namespace);
        $namespace->appendIf(!$namespace->endsWith('\\\\'), '\\\\');

        $path = new StringBuffer($path);
        $path->appendIf(!$path->endsWith('/'), '/');

        $this->content[ $key ][ self::PSR4 ] = [$namespace->toString() => $path->toString()];
    }

    /**
     * @param string $class
     */
    public function addProvider(string $class)
    {
        $this->content[ self::EXTRA ][ self::LARAVEL ][ self::EXTRA_PROVIDERS ][] = $class;
    }

    /**
     * @param string $alias
     * @param string $class
     */
    public function addAlias(string $alias, string $class)
    {
        $this->content[ self::EXTRA ][ self::LARAVEL ][ self::EXTRA_ALIASES ][ $alias ] = $class;
    }

    /**
     * @param string $class
     */
    public function addDontDiscover(string $class)
    {
        $this->content[ self::EXTRA ][ self::LARAVEL ][ self::EXTRA_DONT_DISCOVER ][] = $class;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return json_encode($this->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param string|null $fileName
     *
     * @return bool|int
     */
    public function write(string $fileName = null)
    {
        if (is_null($fileName)) {
            $fileName = $this->composerFile;
        }

        return file_put_contents($fileName, $this->toString());
    }
}