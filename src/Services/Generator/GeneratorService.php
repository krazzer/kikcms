<?php declare(strict_types=1);


namespace KikCMS\Services\Generator;


use KikCMS\Classes\Phalcon\Injectable;
use KikCMS\Config\KikCMSConfig;
use KikCmsCore\Classes\ObjectList;
use KikCmsCore\Classes\ObjectMap;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

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

        $formClass        = $className . 'Form';
        $dataTableClass   = $className . 's';
        $objectListClass  = $className . 'List';
        $objectMapClass   = $className . 'Map';
        $serviceClassName = $className . 'Service';

        $classesToGenerate = [
            [KikCMSConfig::NAMESPACE_PATH_MODELS, 'model', [$className, $table]],
            [KikCMSConfig::NAMESPACE_PATH_FORMS, 'form', [$formClass, $className]],
            [KikCMSConfig::NAMESPACE_PATH_DATATABLES, 'dataTable', [$dataTableClass, $table, $className, $formClass]],
            [KikCMSConfig::NAMESPACE_PATH_OBJECTLIST, 'objectList', [$objectListClass, $className, ObjectList::class]],
            [KikCMSConfig::NAMESPACE_PATH_OBJECTLIST, 'objectList', [$objectMapClass, $className, ObjectMap::class]],
            [KikCMSConfig::NAMESPACE_PATH_SERVICES, 'service', [$serviceClassName]],
        ];

        foreach ($classesToGenerate as list($namespace, $method, $parameters)) {
            if ( ! class_exists($namespace . $parameters[0])) {
                $method = 'create' . ucfirst($method) . 'Class';
                call_user_func_array([$this->classesGeneratorService, $method], $parameters);
            }
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

        if ( ! file_exists($fileDir)) {
            mkdir($fileDir);
        }

        $printer = new PsrPrinter();

        return (bool) file_put_contents($filePath, "<?php declare(strict_types=1);\n\n" . $printer->printNamespace($namespace));
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