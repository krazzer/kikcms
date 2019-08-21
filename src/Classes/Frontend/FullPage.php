<?php declare(strict_types=1);

namespace KikCMS\Classes\Frontend;

use DateTime;
use KikCMS\Models\Page;
use KikCMS\Models\PageLanguage;
use KikCmsCore\Classes\Identifiable;

/**
 * A FullPage represents a Page in a certain language, with all field content
 */
class FullPage extends Identifiable
{
    /** @var Page */
    private $page;

    /** @var PageLanguage */
    private $pageLanguage;

    /** @var array */
    private $content;

    /** @var string */
    private $url;

    /**
     * @param Page $page
     * @param PageLanguage $pageLanguage
     * @param array $content
     * @param string $url
     */
    public function __construct(Page $page, PageLanguage $pageLanguage, array $content, string $url)
    {
        $this->setId($pageLanguage->getPageId());

        $this->page         = $page;
        $this->pageLanguage = $pageLanguage;
        $this->content      = $content;
        $this->url          = $url;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $fieldName = lcfirst(str_replace('get', '', $name));

        return $this->get($fieldName);
    }

    /**
     * @param string $fieldName
     * @return mixed
     */
    public function get(string $fieldName)
    {
        if ( ! array_key_exists($fieldName, $this->content)) {
            return null;
        }

        return $this->content[$fieldName];
    }

    /**
     * @return null|string
     */
    public function getKey(): ?string
    {
        return (string) $this->getPage()->key;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->getPageLanguage()->getLanguageCode();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->getPage()->getLevel();
    }

    /**
     * @return int|null
     */
    public function getLink(): ?int
    {
        return $this->getPage()->getLink();
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageId(): int
    {
        return $this->getPageLanguage()->getPageId();
    }

    /**
     * @return PageLanguage
     */
    public function getPageLanguage(): PageLanguage
    {
        return $this->pageLanguage;
    }

    /**
     * @return null|int
     */
    public function getParentId(): ?int
    {
        return $this->getPage()->getParentId();
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return DateTime
     */
    public function getCreatedDate(): DateTime
    {
        return $this->page->getCreatedDate();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getPageLanguage()->getName();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param array $content
     */
    public function setContent(array $content)
    {
        $this->content = $content;
    }

    /**
     * @param Page $page
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @param PageLanguage $pageLanguage
     */
    public function setPageLanguage(PageLanguage $pageLanguage)
    {
        $this->pageLanguage = $pageLanguage;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }
}