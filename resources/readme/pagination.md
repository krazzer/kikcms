# How to add pagination to a page

1. Add to `app/Classes/WebsiteSettings::addFrontendRoutes`:
```php
if($indexedPageUrl = $this->urlService->getUrlByPageKey('PAGE_KEY')){
    $frontend->add('{url:' . $newsPageUrl . '}/{page:[0-9]+}', KikCMSConfig::NAMESPACE_PATH_CMS_CONTROLLERS . 'Frontend::page')->setName('PAGE_ROUTE_NAME');
}
```
2. Replace `PAGE_KEY` with the key of the page that needs pagination. Replace `PAGE_ROUTE_NAME` with a desired route name.
3. Copy to `app/Classes/TemplateVariables.php` in a function for the corresponding template:
```php
$page      = $this->dispatcher->getParam('page') ?: 1;
$pageCount = $this->someService->getPageCount();
$itemMap   = $this->someService->getMap($page);
$pages     = $this->paginateListService->getPageList($pageCount, $page);

return [
    'itemMap'     => $itemMap,
    'pages'       => $pages,
    'currentPage' => $page,
];
```
4. Replace `someService` with your own.
5. In your created service, make the functions to retrieve the paginated content, for example:
```php
/**
  * @param int $page
  * @return FullPageMap
  */
 public function getMap(int $page): FullPageMap
 {
     // todo: this is an example, modify with your own logic
     $query = $this->getNewsBaseQuery()
         ->limit(Config::ITEMS_PER_PAGE, ($page - 1) * Config::ITEMS_PER_PAGE);

     $pageMap = $this->dbService->getObjectMap($query, PageMap::class);

     return $this->fullPageService->getByPageMap($pageMap);
 }

 /**
  * @return Builder
  */
 public function getBaseQuery(): Builder
 {
     // todo: write a query
 }

 /**
  * @return int
  */
 public function getPageCount(): int
 {
     $query = $this->getBaseQuery()->columns('COUNT(id)');

     return ceil($this->dbService->getValue($query) / Config::ITEMS_PER_PAGE);
 }
```
6. Where you make a config variable `ITEMS_PER_PAGE` and you replace, and you add your own logic.
7. In the template, you add this code to add the pages:
```html
<ul class="pagination">
    {% for pageIndex in pages %}
        {% if pageIndex %}
            <li class="{{ pageIndex == currentPage ? 'active' : '' }}">
                <a href="{{ urlPath }}/{{ pageIndex }}" data-page="{{ pageIndex }}">{{ pageIndex }}</a>
            </li>
        {% else %}
            <li class="disabled"><a>...</a></li>
        {% endif %}
    {% endfor %}
</ul>
```