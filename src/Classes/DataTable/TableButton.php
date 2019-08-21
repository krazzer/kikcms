<?php declare(strict_types=1);

namespace KikCMS\Classes\DataTable;

/**
 * Button that will be displayed in the table to add an action to a row
 */
class TableButton
{
    /** @var string */
    private $icon;

    /** @var string */
    private $title;

    /** @var string */
    private $class;

    /** @var string */
    private $url;

    /**
     * @param string $icon
     * @param string $title
     * @param string $class
     * @param string|null $url
     * @param bool $blank
     */
    public function __construct(string $icon, string $title, string $class, string $url = null, bool $blank = false)
    {
        $this->icon  = $icon;
        $this->title = $title;
        $this->class = $class;
        $this->url   = $url;
        $this->blank = $blank;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param array $row
     * @return string
     */
    public function getUrl(array $row): string
    {
        $url = $this->url;

        foreach ($row as $key => $value) {
            $url = str_replace(':' . $key, $value, $url);
        }

        return $url;
    }

    /**
     * @return bool
     */
    public function hasUrl(): bool
    {
        return (bool) $this->url;
    }

    /**
     * @return bool
     */
    public function isBlank(): bool
    {
        return $this->blank;
    }

    /**
     * @param bool $blank
     * @return TableButton
     */
    public function setBlank(bool $blank): TableButton
    {
        $this->blank = $blank;
        return $this;
    }
}