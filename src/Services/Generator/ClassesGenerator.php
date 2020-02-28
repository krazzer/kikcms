<?php declare(strict_types=1);


namespace KikCMS\Services\Generator;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\KikCMSConfig;
use KikCmsCore\Classes\Model;
use Nette\PhpGenerator\PhpNamespace;

class ClassesGenerator extends Injectable
{
    /** @var string */
    private $objectName;

    /**
     * ClassesGenerator constructor.
     * @param string $objectName
     */
    public function __construct(string $objectName)
    {
        $this->objectName = $objectName;
    }

    /**
     * @param string $className
     * @return bool
     */
    public function createServiceClass(string $className): bool
    {
        $namespace = $this->createNamespace();

        $namespace->addUse(Injectable::class);

        $class = $namespace->addClass($className);

        $class->setExtends(Injectable::class);

        return $this->generatorService->createFile($this->getDirectory(), $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $table
     * @return bool
     */
    public function createModelClass(string $className, string $table): bool
    {
        $alias = $this->generatorService->getTableAlias($table);

        $namespace = $this->createNamespace();

        $namespace->addUse(Model::class);

        $class = $namespace->addClass($className)
            ->setExtends(Model::class);

        $class->addConstant('TABLE', $table);
        $class->addConstant('ALIAS', $alias);

        $columns = $this->generatorService->getTableColumns($table);

        foreach ($columns as $column) {
            $class->addConstant('FIELD_' . strtoupper($column), $column);
        }

        $class->addMethod('initialize')
            ->addComment('@inheritdoc')
            ->addBody('parent::initialize();');

        return $this->generatorService->createFile($this->getDirectory(), $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $table
     * @param string $modelClassName
     * @param string $formClassName
     * @return bool
     */
    public function createDataTableClass(string $className, string $table, string $modelClassName, string $formClassName): bool
    {
        $namespace = $this->createNamespace();

        $namespace->addUse(DataTable::class);

        $class = $namespace->addClass($className);

        $class->setExtends(DataTable::class);

        $class->addMethod('getFormClass')
            ->addComment('@inheritdoc')
            ->setReturnType('string')
            ->setBody('return ' . $formClassName . '::class;');

        $class->addMethod('getLabels')
            ->addComment('@inheritdoc')
            ->setReturnType('array')
            ->addBody("return ['" . strtolower($modelClassName) . "', '" . strtolower($modelClassName) . "s'];");

        $class->addMethod('getModel')
            ->addComment('@inheritdoc')
            ->setReturnType('string')
            ->setBody('return ' . $modelClassName . '::class;');

        $tableFieldMapMethod = $class->addMethod('getTableFieldMap')
            ->addComment('@inheritdoc')
            ->setReturnType('array')
            ->addBody('return [');

        $class->addMethod('initialize')
            ->addComment('@inheritdoc')
            ->setBody('// nothing here...');

        $columns = $this->generatorService->getTableColumns($table);

        foreach ($columns as $column) {
            $tableFieldMapMethod->addBody('    ' . $modelClassName . '::FIELD_' . strtoupper($column) . ' => \'' . ucfirst($column) . '\',');
        }

        $tableFieldMapMethod->addBody('];');

        return $this->generatorService->createFile($this->getDirectory(), $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $modelClassName
     * @return bool
     */
    public function createFormClass(string $className, string $modelClassName): bool
    {
        $namespace = $this->createNamespace();

        $namespace->addUse(DataForm::class);

        $class = $namespace->addClass($className);

        $class->setExtends(DataForm::class);

        $class->addMethod('getModel')
            ->addComment('@inheritdoc')
            ->addBody('return ' . $modelClassName . '::class;')
            ->setReturnType('string');

        $class->addMethod('initialize')
            ->addComment('@inheritdoc')
            ->setVisibility('protected')
            ->setBody('// add form code...');

        return $this->generatorService->createFile($this->getDirectory(), $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $modelClassName
     * @param string $typeClass
     * @return bool
     */
    public function createObjectListClass(string $className, string $modelClassName, string $typeClass): bool
    {
        $namespace = $this->createNamespace();

        $namespace->addUse($typeClass);

        $class = $namespace->addClass($className)
            ->setExtends($typeClass);

        $class->addMethod('current')
            ->setBody('')
            ->addComment('@inheritdoc')
            ->addComment('@return ' . $modelClassName . '|false')
            ->setBody('return parent::current();');

        $class->addMethod('get')
            ->setBody('')
            ->addComment('@inheritdoc')
            ->addComment('@return ' . $modelClassName . '|false')
            ->setBody('return parent::get($key);')
            ->addParameter('key');

        $class->addMethod('getFirst')
            ->setBody('')
            ->addComment('@inheritdoc')
            ->addComment('@return ' . $modelClassName . '|false')
            ->setBody('return parent::getFirst();');

        $class->addMethod('getLast')
            ->setBody('')
            ->addComment('@inheritdoc')
            ->addComment('@return ' . $modelClassName . '|false')
            ->setBody('return parent::getLast();');

        return $this->generatorService->createFile($this->getDirectory(), $className, $namespace);
    }

    /**
     * @return PhpNamespace
     */
    private function createNamespace(): PhpNamespace
    {
        return new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_OBJECTS . $this->objectName, '\\'));
    }

    /**
     * @return string
     */
    private function getDirectory(): string
    {
        return 'Objects/' . $this->objectName;
    }
}