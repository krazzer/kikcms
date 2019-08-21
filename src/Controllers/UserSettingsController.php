<?php declare(strict_types=1);


namespace KikCMS\Controllers;


use Exception;
use KikCMS\Services\Cms\UserSettingsService;
use Monolog\Logger;

/**
 * @property UserSettingsService $userSettingsService
 * @property Logger $logger
 */
class UserSettingsController extends BaseCmsController
{
    public function updateClosedPageIdsAction()
    {
        $ids       = $this->request->getPost('ids', 'int', []);
        $className = $this->request->getPost('className', 'string');

        try {
            $this->userSettingsService->storeClosedPageIds($className, $ids);
            $success = true;
        } catch (Exception $e) {
            $this->logger->log(Logger::ERROR, $e);
            $success = false;
        }

        return $this->response->setJsonContent([
            'success' => $success
        ]);
    }
}