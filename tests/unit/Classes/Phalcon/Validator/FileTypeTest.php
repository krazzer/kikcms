<?php
declare(strict_types=1);

namespace unit\Classes\Phalcon\Validator;

use Codeception\Test\Unit;
use Helpers\TestHelper;
use KikCMS\Classes\Phalcon\Validator\FileType;
use KikCMS\Models\File;
use KikCmsCore\Services\DbService;
use Phalcon\Validation;

class FileTypeTest extends Unit
{
    public function testValidate()
    {
        $di = (new TestHelper)->getTestDi();

        /** @var DbService $dbService */
        $dbService = $di->get('dbService');

        $dbService->truncate(File::class);

        $validation = new Validation();

        $validation->add('file_id', new FileType([FileType::OPTION_FILETYPES => ['jpg']]));

        // test file doesnt exist
        $messages = $validation->validate(['file_id' => 1]);
        $this->assertCount(1, $messages);

        // test file is valid
        $dbService->insert(File::class, ['id' => 1, 'extension' => 'jpg', 'mimetype' => 'image/jpg']);
        $messages = $validation->validate(['file_id' => 1]);
        $this->assertCount(0, $messages);

        // test file is invalid
        $dbService->update(File::class, ['extension' => 'pdf'], ['id' => 1]);
        $messages = $validation->validate(['file_id' => 1]);
        $this->assertCount(1, $messages);

        $dbService->truncate(File::class);
    }
}
