<?php

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

    /**
     * @param string $icon
     * @param string $title
     * @param string $class
     */
    public function __construct(string $icon, string $title, string $class)
    {
        $this->icon  = $icon;
        $this->title = $title;
        $this->class = $class;
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
}