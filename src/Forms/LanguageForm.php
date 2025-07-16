<?php declare(strict_types=1);

namespace KikCMS\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use KikCMS\Config\CacheConfig;
use KikCMS\Models\Language;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;

/**
 * @property AdapterInterface $cache
 */
class LanguageForm extends DataForm
{
    /**
     * @inheritdoc
     */
    protected function initialize(): void
    {
        $strLength = new StringLength(['max' => 1, 'min' => 3]);

        $this->addTextField(Language::FIELD_NAME, $this->translator->tl('fields.name'), [new PresenceOf()]);
        $this->addTextField(Language::FIELD_CODE, $this->translator->tl('fields.code'), [new PresenceOf(), $strLength]);
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
    protected function onSave(): void
    {
        $this->cache->delete(CacheConfig::LANGUAGES);
    }
}