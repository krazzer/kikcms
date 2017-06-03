<?php

namespace KikCMS\Classes\WebForm\DataForm\FieldStorage;


use KikCMS\Classes\DbService;
use KikCMS\Classes\WebForm\DataForm\FieldStorage;
use KikCMS\Models\TranslationKey;
use KikCMS\Services\TranslationService;
use Phalcon\Mvc\Model\Query\Builder;

/**
 * @property TranslationService $translationService
 * @property DbService $dbService
 */
class Translation extends FieldStorage
{
    /** @var string|null if the datable itself is not multilingual, this can be set to force a field to translate for a certain language */
    private $languageCode = null;

    /**
     * @inheritdoc
     */
    public function store($value, $relationId, $languageCode = null)
    {
        $languageCode = $this->languageCode ?: $languageCode;

        $translationKeyId = $this->getTranslationKeyId($relationId);

        $this->translationService->saveValue($value, $translationKeyId, $languageCode);

        $set = [$this->field->getTableField() => $translationKeyId];
        $this->dbService->update($this->getTableModel(), $set, ['id' => $relationId]);
    }

    /**
     * @inheritdoc
     */
    public function getValue($relationId, $languageCode = null)
    {
        $languageCode = $this->languageCode ?: $languageCode;

        $translationKeyId = $this->getTranslationKeyId($relationId);

        return $this->translationService->getTranslationValue($translationKeyId, $languageCode);
    }

    /**
     * @param null|string $languageCode
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * @return int
     */
    private function createNewTranslationKeyId()
    {
        $translationKey = new TranslationKey();

        $translationKey->db = true;
        $translationKey->save();

        return (int) $translationKey->id;
    }

    /**
     * @param int $relationId
     * @return int
     */
    private function getTranslationKeyId(int $relationId): int
    {
        $query = (new Builder())
            ->from($this->getTableModel())
            ->columns($this->field->getTableField())
            ->where('id = :id:', ['id' => $relationId]);

        $translationKeyId = $this->dbService->getValue($query);

        if ($translationKeyId) {
            return $translationKeyId;
        }

        return $this->createNewTranslationKeyId();
    }
}