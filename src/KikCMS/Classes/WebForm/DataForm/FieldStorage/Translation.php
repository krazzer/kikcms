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
    /** @var int|null */
    private $groupId = null;

    /**
     * @inheritdoc
     */
    public function store($value, $relationId, $languageCode = null)
    {
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
        $translationKeyId = $this->getTranslationKeyId($relationId);

        return $this->translationService->getTranslationValue($translationKeyId, $languageCode);
    }

    /**
     * @return int|null
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int|null $groupId
     * @return Translation
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return int
     */
    private function createNewTranslationKeyId()
    {
        $translationKey = new TranslationKey();

        $translationKey->group_id = $this->getGroupId();
        $translationKey->db       = true;

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