<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
use Phalcon\Cache\Backend;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;

/**
 * @property Backend $cache
 */
class LanguageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->addTextField(Language::FIELD_NAME, $this->translator->tl('fields.name'), [new PresenceOf()]);
        $this->addTextField(Language::FIELD_CODE, $this->translator->tl('fields.code'), [new PresenceOf(), new StringLength(['max' => 2, 'min' => 2])]);
        $this->addCheckboxField(Language::FIELD_ACTIVE, 'Actief op website');
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return Language::class;
    }

    /**
     * @inheritdoc
     */
    protected function onSave()
    {
        $this->cache->delete(CacheConfig::LANGUAGES);
    }
}