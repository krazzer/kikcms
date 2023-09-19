<?php declare(strict_types=1);

namespace KikCMS\Classes\WebForm;

use Exception;
use KikCMS\Classes\WebForm\Fields\CheckboxField;
use KikCMS\Classes\WebForm\Fields\FileInputField;
use KikCMS\Classes\WebForm\Fields\HiddenField;
use KikCMS\Classes\WebForm\Fields\ReCaptchaField;
use KikCMS\Classes\WebForm\Fields\SelectField;
use KikCMS\Services\MailService;
use Monolog\Logger;
use Phalcon\Http\Request\File;
use Phalcon\Http\Response;
use ReCaptcha\Response as ReCaptchaResponse;
use Swift_Attachment;
use Swift_ByteStream_FileByteStream;
use Swift_IoException;

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
        return $this->translator->translateDefaultLanguage('mailForm.subject');
    }

    /**
     * @return string|array
     */
    protected function getToAddress(): array|string
    {
        return $this->config->application->adminEmail;
    }

    /**
     * @param array $input
     * @return Response|string|null
     */
    protected function successAction(array $input): null|Response|string
    {
        $params = [];

        if ($this->getSpamScore() && $this->getSpamScore() <= 0.3) {
            $this->flash->error($this->translator->tl('mailForm.sendFail'));
            return null;
        }

        $body = $this->mailFormService->getHtml($this->getReadableInput($input));

        $attachments = $this->getAttachments();
        $to          = $this->getToAddress();
        $subject     = $this->getSubject();

        if ($spamScore = $this->getSpamScore()) {
            if ($spamScore < 0.9) {
                $subject = $subject . ' (spamscore: ' . $spamScore . ')';
            }
        }

        $mailSend = $this->mailService->sendServiceMail($to, $subject, $body, $params, $attachments);

        if ( ! $mailSend) {
            $this->flash->error($this->translator->tl('mailForm.sendFail'));
            return null;
        }

        try {
            $this->mailformSubmissionService->add($subject, $this->getReadableInput($input, $this->request->getUploadedFiles(true)));
        } catch (Exception $exception) {
            $this->logger->log(Logger::ERROR, $exception->getMessage(), $exception->getTrace());
        }

        $this->flashForFormOnly();
        $this->flash->success($this->getSuccessMessage());

        return $this->response->redirect(trim($this->request->getServer('REQUEST_URI'), '/') ?: '/');
    }

    /**
     * @param array $input
     * @param File[] $files
     * @return array [label => value]
     */
    public function getReadableInput(array $input, array $files = []): array
    {
        $readableInput = [];

        foreach ($files as $file) {
            $input[$file->getKey()] = $file;
        }

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

            if ($field instanceof FileInputField) {
                $fileId = $this->fileService->create($input[$key], $this->mailformSubmissionService->getUploadsFolderId());
                $url    = $this->twigService->mediaFile($fileId, null, true);

                $input[$key] = '<a href="' . $url . '" target="blank">' . $url . '</a>';
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

            $readableInput[$label] = $value;
        }

        return $readableInput;
    }

    /**
     * @return float|null
     */
    private function getSpamScore(): ?float
    {
        if ( ! $reCaptchaField = $this->getReCaptchaField()) {
            return null;
        }

        if ( ! $response = $this->getReCaptchaResponse($reCaptchaField)) {
            return null;
        }

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

    /**
     * @return Swift_Attachment[]
     * @throws Swift_IoException
     */
    private function getAttachments(): array
    {
        $attachments = [];

        if ( ! $files = $this->request->getUploadedFiles(true)) {
            return [];
        }

        foreach ($files as $file) {
            $data       = new Swift_ByteStream_FileByteStream($file->getTempName());
            $attachment = new Swift_Attachment($data, $file->getName(), $file->getType());

            $attachments[] = $attachment;
        }

        return $attachments;
    }
}