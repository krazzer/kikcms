<?php


namespace KikCMS\Classes\Phalcon;


use KikCMS\Classes\Translator;
use KikCMS\Config\KikCMSConfig;
use Phalcon\Messages\Messages;
use Phalcon\Filter\Validation\Validator\File\AbstractFile;
use Phalcon\Filter\Validation\ValidatorCompositeInterface;
use Phalcon\Filter\Validation\ValidatorInterface;

/**
 * @property Translator $translator
 */
class Validation extends \Phalcon\Filter\Validation
{
    /**
     * @inheritDoc
     */
    public function validate($data = null, $entity = null): Messages
    {
        $validatorFields = $this->getValidators();

        foreach ($validatorFields as $validators) {
            foreach ($validators as $validator) {
                if ($validator instanceof ValidatorCompositeInterface) {
                    foreach ($validator->getValidators() as $subValidator) {
                        $this->setDefaultTemplate($subValidator);
                    }
                } else {
                    $this->setDefaultTemplate($validator);
                }
            }
        }

        return parent::validate($data, $entity);
    }

    /**
     * @param ValidatorInterface $validator
     */
    private function setDefaultTemplate(ValidatorInterface $validator): void
    {
        $className = get_class($validator);

        // if a custom message has been set, that should be used
        if($validator->getOption('message')){
            return;
        }

        // the above check fails for phalcon < 4.1, so also check if the template is different from the default
        if($validator->getTemplate() != (new $className)->getTemplate()){
            return;
        }

        $replaces = [
            KikCMSConfig::NAMESPACE_PATH_PHALCON_VALIDATORS,
            KikCMSConfig::NAMESPACE_PATH_CUSTOM_PHALCON_VALIDATORS,
            '\\'
        ];

        $className = str_replace($replaces, '', $className);

        $translationKey = 'webform.messages.' . $className;

        if($this->translator->exists($translationKey)) {
            $translation = $this->translator->tl($translationKey);
            $validator->setTemplate($translation);
        }

        if($validator instanceof AbstractFile){
            $maxSize = $validator->getOption('size');

            $validator->setMessageFileEmpty($this->translator->tl('webform.messages.PresenceOf'));
            $validator->setMessageIniSize($this->translator->tl('webform.messages.FileSizeMax', ['size' => $maxSize]));
            $validator->setMessageValid($this->translator->tl('webform.messages.FileValid'));
        }
    }
}