<?php


namespace KikCMS\Services\Generator;


use KikCMS\Classes\Phalcon\Loader;
use KikCMS\Config\KikCMSConfig;
use KikCmsCore\Classes\ObjectList;
use KikCmsCore\Classes\ObjectMap;
use KikCmsCore\Services\DbService;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Phalcon\Di\Injectable;

/**
 * @property ClassesGeneratorService $classesGeneratorService
 * @property DbService $dbService
 * @property Loader $loader
 */
class GeneratorService extends Injectable
{
    /**
     * @return bool
     */
    public function generate(): bool
    {
        $tables = $this->getTables();

        foreach ($tables as $table) {
            $this->generateForTable($table);
        }

        return true;
    }

    /**
     * @param string $table
     */
    public function generateForTable(string $table)
    {
        $className = $this->getClassName($table);

        $modelClassName      = $className;
        $formClassName       = $className . 'Form';
        $dataTableClassName  = $className . 's';
        $objectListClassName = $className . 'List';
        $objectMapClassName  = $className . 'Map';
        $serviceClassName    = $className . 'Service';

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_MODELS . $modelClassName)) {
            $this->classesGeneratorService->createModelClass($modelClassName, $table);
        }

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_FORMS . $formClassName)) {
            $this->classesGeneratorService->createFormClass($formClassName, $modelClassName);
        }

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_DATATABLES . $dataTableClassName)) {
            $this->classesGeneratorService->createDataTableClass($dataTableClassName, $table, $modelClassName, $formClassName);
        }

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_OBJECTLIST . $objectListClassName)) {
            $this->classesGeneratorService->createObjectListClass($objectListClassName, $modelClassName, ObjectList::class);
        }

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_OBJECTLIST . $objectMapClassName)) {
            $this->classesGeneratorService->createObjectListClass($objectMapClassName, $modelClassName, ObjectMap::class);
        }

        if ( ! class_exists(KikCMSConfig::NAMESPACE_PATH_SERVICES . $serviceClassName)) {
            $this->classesGeneratorService->createServiceClass($serviceClassName);
        }
    }

    /**
     * @param string $table
     * @return string
     */
    private function getClassName(string $table): string
    {
        $parts = explode('_', $table);

        array_shift($parts);

        $className = implode('', array_map(function ($p) {
            return ucfirst($p);
        }, $parts));

        return $className;
    }

    /**
     * Get an array with table names that ought to be generated models from
     *
     * @return string[]
     */
    private function getTables()
    {
        $tables = $this->dbService->queryValues("SHOW TABLES");

        foreach ($tables as $index => $table) {
            if (substr($table, 0, 3) == 'ga_' || substr($table, 0, 4) == 'cms_' || substr($table, 0, 7) == 'finder_') {
                unset($tables[$index]);
            }
        }

        return $tables;
    }

    /**
     * @param string $directory
     * @param string $className
     * @param PhpNamespace $namespace
     * @return bool
     */
    public function createFile(string $directory, string $className, PhpNamespace $namespace): bool
    {
        $fileDir  = $this->loader->getWebsiteSrcPath() . $directory . '/';
        $filePath = $fileDir . $className . '.php';

        if( ! file_exists($fileDir)){
            mkdir($fileDir);
        }

        $printer = new PsrPrinter();

        return file_put_contents($filePath, "<?php\n\n" . $printer->printNamespace($namespace));
    }

    /**
     * @param string $table
     * @return string
     */
    public function getTableAlias(string $table): string
    {
        $parts = explode('_', $table);

        array_shift($parts);

        return implode('', array_map(function ($p) {
            return substr($p, 0, 1);
        }, $parts));
    }

    /**
     * @param string $table
     * @return string[]
     */
    public function getTableColumns(string $table): array
    {
        return $this->dbService->queryValues('SHOW COLUMNS FROM ' . $table);
    }
}