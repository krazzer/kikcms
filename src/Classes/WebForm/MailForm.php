<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;

use KikCMS\Classes\WebForm\Fields\CheckboxField;
use KikCMS\Classes\WebForm\Fields\HiddenField;
use KikCMS\Classes\WebForm\Fields\ReCaptchaField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Services\MailService;
use Phalcon\Http\Response;
use ReCaptcha\Response as ReCaptchaResponse;

/**
 * @property MailService $mailService
 */
abstract class MailForm extends WebForm
{
    /**
     * @return string
     */
    protected function getSuccessMessage(): string
    {
        return $this->translator->tl('mailForm.sendSuccess');
    }

    /**
     * @return string
     */
    protected function getSubject(): string
    {
        $subject = $this->translator->translateDefaultLanguage('mailForm.subject');

        if ($spamScore = $this->getSpamScore()) {
            if ($spamScore < 0.9) {
                return $subject . ' (spamscore: ' . $spamScore . ')';
            }
        }

        return $subject;
    }

    /**
     * @return string|array
     */
    protected function getToAddress()
    {
        return $this->config->application->adminEmail;
    }

    /**
     * @param array $input
     * @return bool|Response
     */
    protected function successAction(array $input)
    {
        $params = [];

        if ($this->getSpamScore() && $this->getSpamScore() <= 0.3) {
            $this->flash->error($this->translator->tl('mailForm.sendFail'));
            return false;
        }

        $contents = $this->toMailOutput($input);
        $mailSend = $this->mailService->sendServiceMail($this->getToAddress(), $this->getSubject(), $contents, $params);

        if ( ! $mailSend) {
            $this->flash->error($this->translator->tl('mailForm.sendFail'));
            return false;
        }

        $this->flashForFormOnly();
        $this->flash->success($this->getSuccessMessage());

        return $this->response->redirect(trim($this->router->getRewriteUri(), '/'));
    }

    /**
     * @param array $input
     * @return string
     */
    public function toMailOutput(array $input): string
    {
        $contents = '';

        foreach ($this->getFieldMap() as $key => $field) {
            if ($key == $this->getFormId()) {
                continue;
            }

            if ($field instanceof ReCaptchaField) {
                continue;
            }

            if ($field instanceof CheckboxField) {
                $input[$key] = $input[$key] ? '✔︎' : '-';
            }

            if ( ! array_key_exists($key, $input)) {
                continue;
            }

            if ($field instanceof SelectField) {
                $input[$key] = $field->getElement()->getOptions()[$input[$key]];
            }

            if (is_array($input[$key])) {
                $input[$key] = implode("\n", $input[$key]);
            }

            $value = nl2br((string) $input[$key]);
            $value = str_replace("\n", '', $value);

            if ( ! $value) {
                $value = '-';
            }

            if ( ! $field->getElement()) {
                continue;
            }

            if ($field instanceof HiddenField) {
                $label = ucfirst($field->getKey());
            } else {
                $label = $field->getElement()->getLabel();
            }

            $contents .= '<b>' . $label . ':</b><br>';
            $contents .= $value . '<br><br>';
        }

        return $contents;
    }

    /**
     * @return float|null
     */
    private function getSpamScore(): ?float
    {
        if ( ! $reCaptchaField = $this->getReCaptchaField()) {
            return null;
        }

        $response = $this->getReCaptchaResponse($reCaptchaField);

        return $response->getScore();
    }

    /**
     * @return ReCaptchaField|null
     */
    private function getReCaptchaField(): ?ReCaptchaField
    {
        foreach ($this->getFieldMap() as $field) {
            if ($field instanceof ReCaptchaField && $field->getVersion() == 3) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param ReCaptchaField|null $reCaptchaField
     * @return ReCaptchaResponse|null
     */
    private function getReCaptchaResponse(?ReCaptchaField $reCaptchaField): ?ReCaptchaResponse
    {
        $validators = $this->validation->getValidators();

        foreach ($validators as $validator) {
            if ($validator[0] == $reCaptchaField->getKey()) {
                return $validator[1]->getOption('response');
            }
        }

        return null;
    }
}