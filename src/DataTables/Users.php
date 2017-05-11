<?php

namespace KikCMS\DataTables;


use KikCMS\Classes\DataTable\DataTable;
use KikCMS\Forms\UserForm;
use KikCMS\Models\KikcmsUser;

class Users extends DataTable
{
    /**
     * @inheritdoc
     */
    public function getDefaultQuery()
    {
        return parent::getDefaultQuery()->columns([
            KikcmsUser::FIELD_ID,
            KikcmsUser::FIELD_EMAIL,
            KikcmsUser::FIELD_ACTIVE
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getModel(): string
    {
        return KikcmsUser::class;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string
    {
        return UserForm::class;
    }

    /**
     * @inheritdoc
     */
    public function getLabels(): array
    {
        return [
            $this->translator->tl('dataTables.users.singular'),
            $this->translator->tl('dataTables.users.plural')
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getTableFieldMap(): array
    {
        return [
            KikcmsUser::FIELD_ID     => $this->translator->tl('fields.id'),
            KikcmsUser::FIELD_EMAIL  => $this->translator->tl('fields.email'),
            KikcmsUser::FIELD_ACTIVE => $this->translator->tl('fields.active'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function initialize()
    {
        $this->setFieldFormatting(KikcmsUser::FIELD_ACTIVE, function($value){
            return $value == 1 ?
                '<span style="color: green;" class="glyphicon glyphicon-ok"></span>' :
                '<span style="color: #A00000;" class="glyphicon glyphicon-remove"></span>';
        });

        $this->addTableButton('glyphicon glyphicon-link', $this->translator->tl('dataTables.users.activationLink'), 'link');
    }
}