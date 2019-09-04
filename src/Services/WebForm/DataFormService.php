<?php
declare(strict_types=1);

namespace KikCMS\Services\WebForm;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Services\ModelService;
use Phalcon\Di\Injectable;

/**
 * @property ModelService $modelService
 */
class DataFormService extends Injectable
{
    /**
     * @param DataForm $form
     * @return null|string
     */
    public function getObjectName(DataForm $form): ?string
    {
        if( ! $object = $form->getObject()){
            return null;
        }

        return $this->modelService->getObjectName($object);
    }
}