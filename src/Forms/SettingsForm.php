<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\Phalcon\AccessControl;
use KikCMS\Classes\WebForm\WebForm;
use KikCMS\Config\KikCMSConfig;
use KikCMS\DataTables\Languages;
use KikCMS\DataTables\Translations;

/**
 * @property AccessControl $acl
 */
class SettingsForm extends WebForm
{
    /**
     * @inheritdoc
     */
    public function initialize()
    {
        if($this->acl->allowed(Languages::class)) {
            $this->addDataTableField('languages', Languages::class, $this->translator->tl("fields.languages"));
        }

        $this->addDataTableField('translations', Translations::class, $this->translator->tl("fields.translations"));
        $this->addCheckboxField('maintenance', $this->translator->tl("maintenance.checkboxLabel"))
            ->setDefault($this->keyValue->get(KikCMSConfig::SETTING_MAINTENANCE))
            ->setHelpText($this->translator->tl("maintenance.helpText"));
    }

    /**
     * @inheritDoc
     */
    public function getSendButtonLabel(): string
    {
        return $this->translator->tl('dataTable.save');
    }

    /**
     * @inheritDoc
     */
    public function successAction(array $input)
    {
        $this->keyValue->set(KikCMSConfig::SETTING_MAINTENANCE, (bool) $input['maintenance']);
        $this->flash->success($this->translator->tl('dataForm.saveSuccess'));
    }
}