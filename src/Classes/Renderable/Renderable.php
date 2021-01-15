<?php declare(strict_types=1);

namespace KikCMS\Classes\Renderable;


use KikCMS\Classes\Phalcon\Injectable;
use Phalcon\Http\Response;

/**
 * A renderable object
 * E.g. a Form or a DataTable
 */
abstract class Renderable extends Injectable
{
    const FILTER_INSTANCE = 'renderableInstance';
    const FILTER_CLASS    = 'renderableClass';

    /** @var Filters */
    protected $filters;

    /** @var string where views for this object should be stored */
    protected $viewDirectory;

    /** @var string */
    protected $indexView = 'index';

    /** @var string provide a prefix to easily identify an instance */
    protected $instancePrefix;

    /** @var string contains the corresponding js Class for this Renderable */
    protected $jsClass;

    /** @var string unique identifier for this instance */
    private $instance = '';

    /**
     * Renderable constructor.
     * @param Filters|null $filters
     */
    public function __construct(Filters $filters = null)
    {
        if ($filters == null) {
            $filters = $this->getEmptyFilters();
        }

        $this->filters = $filters;
    }

    /**
     * @return Filters
     */
    public abstract function getEmptyFilters(): Filters;

    /**
     * Renders the object
     *
     * @return Response|string
     */
    public abstract function render();

    /**
     * This method may contain logic that will influence the output when rendered
     */
    protected abstract function initialize();

    /**
     * Renders a specific view for a part of the Renderable object
     *
     * @param string $view
     * @param array $parameters
     *
     * @return string
     */
    public function renderView(string $view, array $parameters): string
    {
        return $this->view->getPartial($this->viewDirectory . '/' . $view, $parameters);
    }

    /**
     * @return Filters
     */
    public function getFilters(): Filters
    {
        return $this->filters;
    }

    /**
     * @return string
     */
    public function getIndexView(): string
    {
        return $this->viewDirectory . '/' . $this->indexView;
    }

    /**
     * @return string
     */
    public function getInstance(): string
    {
        if ( ! $this->instance) {
            $this->instance = uniqid($this->instancePrefix);
        }

        return $this->instance;
    }

    /**
     * @return array
     */
    protected function getJsData()
    {
        $properties = [
            self::FILTER_INSTANCE => $this->getInstance(),
            self::FILTER_CLASS    => static::class
        ];

        $properties = array_merge($properties, $this->getJsProperties());

        return [
            'class'      => $this->jsClass,
            'properties' => $properties,
        ];
    }

    /**
     * @return array
     */
    protected function getJsProperties(): array
    {
        return [];
    }

    /**
     * @param Filters $filters
     * @return Renderable
     */
    public function setFilters(Filters $filters): Renderable
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param string $instance
     */
    public function setInstance(string $instance)
    {
        $this->instance = $instance;
    }
}