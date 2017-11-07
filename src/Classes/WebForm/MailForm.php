<?php

namespace KikCMS\Classes\WebForm;

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
    protected function getSubject(): string
    {
        return $this->translator->translateDefaultLanguage('mailForm.subject');
    }

    /**
     * @param array $input
     * @return bool|Response
     */
    protected function successAction(array $input)
    {
        $adminEmail = $this->config->website->adminEmail;

        $from     = $this->getFrom($input);
        $contents = $this->toMailOutput($input);
        $mailSend = $this->mailService->sendServiceMail($adminEmail, $this->getSubject(), $contents, [], $from);

        if( ! $mailSend){
            $this->flash->error($this->translator->tl('mailForm.sendFail'));
            return false;
        }

        $this->flash->success($this->translator->tl('mailForm.sendSuccess'));
        return $this->response->redirect(trim($this->router->getRewriteUri(), '/'));
    }

    /**
     * @param array $input
     * @return string
     */
    private function toMailOutput(array $input): string
    {
        $contents = '';

        foreach ($this->getFieldMap() as $key => $field)
        {
            if($key == WebForm::WEB_FORM_ID){
                continue;
            }

            $value = nl2br($input[$key]);

            if( ! $value){
                $value = '-';
            }

            $contents .= '<b>' . $field->getElement()->getLabel() . ':</b><br>';
            $contents .= $value . '<br><br>';
        }

        return $contents;
    }

    /**
     * @param array $input
     * @return null|string|array
     */
    private function getFrom(array $input)
    {
        if( ! isset($input['email'])) {
            return null;
        }

        if( ! isset($input['name'])) {
            return $input['email'];
        }

        return [$input['email'] => $input['name']];
    }
}