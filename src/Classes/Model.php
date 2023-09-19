<?php
declare(strict_types=1);

namespace KikCMS\Classes;


use Exception;
use Monolog\Logger;

class Model extends \KikCmsCore\Classes\Model
{
    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        try{
            return parent::save();
        } catch (Exception $exception){
            $this->getDI()->get('logger')->log(Logger::ERROR, $exception);
            return false;
        }
    }

    /**
     * Save, but throw errors instead of returning false
     */
    public function saveThrow(): bool
    {
        return parent::save();
    }
}