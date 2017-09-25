<?php declare(strict_types=1);

namespace Simlux\LaravelGenerators\Generators;

use File;
use InspectYourWeb\Helpers\ArrayHelper;
use View;

/**
 * Class ClassGenerator
 *
 * @package InspectYourWeb\Generator
 */
class ClassGenerator
{
    const VISIBILITY_PRIVATE   = 'private';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PUBLIC    = 'public';

    const TYPE_INT    = 'int';
    const TYPE_ARRAY  = 'array';
    const TYPE_STRING = 'string';
    const TYPE_BOOL   = 'bool';
    /**
     * @var string
     */
    private $viewNamespaceName;

    /**
     * @var string
     */
    private $viewNamespacePath;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $templateData = [];

    /**
     * @var string
     */
    private $classFile;

    /**
     * ClassGenerator constructor.
     *
     * @param string $classFile
     * @param string $template
     * @param string $viewNamespaceName
     * @param string $viewNamespacePath
     */
    public function __construct(string $classFile, string $template, string $viewNamespaceName, string $viewNamespacePath)
    {
        $this->classFile = $classFile;
        $this->template  = $template;
        $this->setViewNamespace($viewNamespaceName, $viewNamespacePath);
    }

    private function checkDependencies(): bool
    {
        return true;
    }

    public function generate()
    {
        if (!$this->checkDependencies()) {
            // @TODO
            throw new \Exception('');
        }

        View::addNamespace($this->viewNamespaceName, $this->viewNamespacePath);
        $content = View::make(sprintf('%s::%s', $this->viewNamespaceName, $this->template), $this->templateData)->render();
        File::put($this->classFile, '<?php ' . $content);
    }

    /**
     * @param string $name
     * @param string $path
     */
    public function setViewNamespace(string $name, string $path)
    {
        $this->viewNamespaceName = $name;
        $this->viewNamespacePath = $path;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function addTemplateVar(string $key, $value)
    {
        $this->templateData[ $key ] = $value;
    }

    /**
     * @param bool $bool
     *
     * @return ClassGenerator
     */
    public function strictTypes(bool $bool = true): ClassGenerator
    {
        $this->templateData['strictTypes'] = $bool;

        return $this;
    }

    /**
     * @param string $namespace
     *
     * @return ClassGenerator
     */
    public function namespace(string $namespace): ClassGenerator
    {
        $this->templateData['namespace'] = $namespace;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return ClassGenerator
     */
    public function extends(string $class): ClassGenerator
    {
        $this->templateData['extends'] = $class;

        return $this;
    }

    /**
     * @param string $class
     *
     * @return ClassGenerator
     */
    public function use(string $class): ClassGenerator
    {
        if (!isset($this->templateData['uses'])) {
            $this->templateData['uses'] = [];
        }

        if (!in_array($class, $this->templateData['uses'])) {
            $this->templateData['uses'][] = $class;
        }

        return $this;
    }

    /**
     * @param string $class
     *
     * @return ClassGenerator
     */
    public function trait(string $class): ClassGenerator
    {
        if (!isset($this->templateData['traits'])) {
            $this->templateData['traits'] = [];
        }

        if (!in_array($class, $this->templateData['traits'])) {
            $this->templateData['traits'][] = $class;
        }

        return $this;
    }

    /**
     * @param string      $name
     * @param string|null $type
     *
     * @return ClassGenerator
     */
    public function addProperty(string $name, string $type = null)
    {
        if (!isset($this->templateData['properties'])) {
            $this->templateData['properties'] = [];
        }

        $this->templateData['properties'][ $name ] = is_null($type) ? gettype($type) : $type;

        return $this;
    }

    /**
     * @param string      $visibility
     * @param string      $name
     * @param string|null $type
     * @param null        $default
     *
     * @return ClassGenerator
     * @throws \Exception
     */
    public function addVar(string $visibility, string $name, string $type = null, $default = null): ClassGenerator
    {
        if (!isset($this->templateData['vars'])) {
            $this->templateData['vars'] = [];
        }

        switch (gettype($default)) {
            case 'string':
                if ($type !== 'array') {
                    $default = sprintf("'%s'", $default);
                }
                break;

            case 'boolean':
                $default = $default === true ? 'true' : 'false';
                break;

            case 'array':
                $default = ArrayHelper::arrayToString($default);
                break;
        }

        $this->templateData['vars'][ $name ] = [
            'visibility' => $visibility,
            'type'       => is_null($type) ? gettype($type) : $type,
            'default'    => $default,
        ];

        return $this;
    }

}