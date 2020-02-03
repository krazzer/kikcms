<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;

use KikCMS\Classes\WebForm\Fields\CheckboxField;
use KikCMS\Classes\WebForm\Fields\ReCaptchaField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Services\MailService;
use Phalcon\Http\Response;

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

        if($this->isSpam()){
            return '*** SPAM *** ' . $subject;
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

            $contents .= '<b>' . $field->getElement()->getLabel() . ':</b><br>';
            $contents .= $value . '<br><br>';
        }

        return $contents;
    }

    /**
     * @return bool
     */
    private function isSpam(): bool
    {
        foreach ($this->getFieldMap() as $field){
            if($field instanceof ReCaptchaField && $field->getVersion() == 3){
                $response = $this->reCaptcha->verify($this->getInput()[$field->getKey()], $_SERVER['REMOTE_ADDR']);

                if($response->getScore() <= 0.5){
                    return true;
                }
            }
        }

        return false;
    }
}