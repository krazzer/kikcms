<?php declare(strict_types=1);


namespace KikCMS\Services\Generator;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\KikCMSConfig;
use KikCmsCore\Classes\Model;
use KikCmsCore\Services\DbService;
use Nette\PhpGenerator\PhpNamespace;
use Phalcon\Di\Injectable;

/**
 * @property GeneratorService $generatorService
 */
class ClassesGeneratorService extends Injectable
{
    /**
     * @param string $className
     */
    public function createServiceClass(string $className)
    {
        $namespace = new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_SERVICES, '\\'));

        $namespace->addUse(DbService::class);
        $namespace->addUse(Injectable::class);

        $class = $namespace->addClass($className);

        $class->setExtends(Injectable::class);
        $class->addComment('@property DbService $dbService');

        $this->generatorService->createFile('Services', $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $table
     */
    public function createModelClass(string $className, string $table)
    {
        $alias = $this->generatorService->getTableAlias($table);

        $namespace = new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_MODELS, '\\'));

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

        $this->generatorService->createFile('Models', $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $table
     * @param string $modelClassName
     * @param string $formClassName
     */
    public function createDataTableClass(string $className, string $table, string $modelClassName, string $formClassName)
    {
        $namespace = new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_DATATABLES, '\\'));

        $namespace->addUse(DataTable::class);
        $namespace->addUse(KikCMSConfig::NAMESPACE_PATH_MODELS . $modelClassName);
        $namespace->addUse(KikCMSConfig::NAMESPACE_PATH_FORMS . $formClassName);

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

        $this->generatorService->createFile('DataTables', $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $modelClassName
     */
    public function createFormClass(string $className, string $modelClassName)
    {
        $namespace = new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_FORMS, '\\'));

        $namespace->addUse(DataForm::class);
        $namespace->addUse(KikCMSConfig::NAMESPACE_PATH_MODELS . $modelClassName);

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

        $this->generatorService->createFile('Forms', $className, $namespace);
    }

    /**
     * @param string $className
     * @param string $modelClassName
     * @param string $typeClass
     */
    public function createObjectListClass(string $className, string $modelClassName, string $typeClass)
    {
        $namespace = new PhpNamespace(trim(KikCMSConfig::NAMESPACE_PATH_OBJECTLIST, '\\'));

        $namespace->addUse($typeClass);
        $namespace->addUse(KikCMSConfig::NAMESPACE_PATH_MODELS . $modelClassName);

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

        $this->generatorService->createFile('ObjectList', $className, $namespace);
    }
}