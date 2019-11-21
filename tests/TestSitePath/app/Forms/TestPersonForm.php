<?php


namespace Website\Forms;


use KikCMS\Classes\WebForm\DataForm\DataForm;
use Phalcon\Validation\Validator\PresenceOf;
use Website\Models\TestPerson;

class TestPersonForm extends DataForm
{

    /**
     * @return string
     */
    public function getModel(): string
    {
        return TestPerson::class;
    }

    /**
     * This method may contain logic that will influence the output when rendered
     */
    protected function initialize()
    {
        $this->addTextField(TestPerson::FIELD_NAME, 'Name', [new PresenceOf]);
        $this->addDateField(TestPerson::FIELD_CREATED, 'Created', [new PresenceOf])->setFormat('d-m-Y');
        $this->addFileField(TestPerson::FIELD_IMAGE_ID, 'Afbeelding');
    }
}