<?php
declare(strict_types=1);

namespace KikCMS\Classes\Phalcon;

use Google_Service_AnalyticsReporting;
use KikCMS\Classes\Frontend\Extendables\WebsiteSettingsBase;
use KikCMS\Classes\ImageHandler\ImageHandler;
use KikCMS\Classes\Permission;
use KikCMS\Classes\Translator;
use KikCMS\Services\Analytics\AnalyticsGoogleService;
use KikCMS\Services\Analytics\AnalyticsImportService;
use KikCMS\Services\Analytics\AnalyticsService;
use KikCMS\Services\AssetService;
use KikCMS\Services\Base\BaseServices;
use KikCMS\Services\CacheService;
use KikCMS\Services\CliService;
use KikCMS\Services\Cms\CmsService;
use KikCMS\Services\Cms\RememberMeService;
use KikCMS\Services\Cms\UserSettingsService;
use KikCMS\Services\DataTable\DataTableFilterService;
use KikCMS\Services\DataTable\DataTableService;
use KikCMS\Services\DataTable\NestedSetService;
use KikCMS\Services\DataTable\PageRearrangeService;
use KikCMS\Services\DataTable\PagesDataTableService;
use KikCMS\Services\DataTable\RearrangeService;
use KikCMS\Services\DataTable\TableDataService;
use KikCMS\Services\DataTable\TinyMceService;
use KikCMS\Services\ErrorService;
use KikCMS\Services\Finder\FileCacheService;
use KikCMS\Services\Finder\FileHashService;
use KikCMS\Services\Finder\FilePermissionHelper;
use KikCMS\Services\Finder\FilePermissionService;
use KikCMS\Services\Finder\FileRemoveService;
use KikCMS\Services\Finder\FileResizeService;
use KikCMS\Services\Finder\FileService;
use KikCMS\Services\Finder\FinderService;
use KikCMS\Services\Generator\GeneratorService;
use KikCMS\Services\LanguageService;
use KikCMS\Services\MailService;
use KikCMS\Services\ModelService;
use KikCMS\Services\NamespaceService;
use KikCMS\Services\Pages\FullPageService;
use KikCMS\Services\Pages\PageContentService;
use KikCMS\Services\Pages\PageLanguageService;
use KikCMS\Services\Pages\PageService;
use KikCMS\Services\Pages\TemplateService;
use KikCMS\Services\Pages\UrlService;
use KikCMS\Services\PlaceholderService;
use KikCMS\Services\Routing;
use KikCMS\Services\Services;
use KikCMS\Services\TranslationService;
use KikCMS\Services\TwigService;
use KikCMS\Services\UserService;
use KikCMS\Services\Util\ByteService;
use KikCMS\Services\Util\DateTimeService;
use KikCMS\Services\Util\JsonService;
use KikCMS\Services\Util\NumberService;
use KikCMS\Services\Util\PaginateListService;
use KikCMS\Services\Util\QueryService;
use KikCMS\Services\Util\StringService;
use KikCMS\Services\VendorCleanUpService;
use KikCMS\Services\WebForm\DataFormService;
use KikCMS\Services\WebForm\RelationKeyService;
use KikCMS\Services\WebForm\StorageService;
use KikCMS\Services\WebForm\WebFormService;
use KikCMS\Services\Website\FrontendHelper;
use KikCMS\Services\Website\FrontendService;
use KikCMS\Services\Website\MenuService;
use KikCmsCore\Services\DbService;
use Monolog\Logger;
use Phalcon\Cache\Backend;
use Phalcon\Validation;
use ReCaptcha\ReCaptcha;

/**
 * @property AccessControl $acl
 * @property AnalyticsGoogleService analyticsGoogleService
 * @property AnalyticsImportService analyticsImportService
 * @property AnalyticsService analyticsService
 * @property CliService cliService
 * @property Google_Service_AnalyticsReporting analytics
 * @property AssetService assetService
 * @property BaseServices baseServices
 * @property ByteService byteService
 * @property Backend cache
 * @property CacheService cacheService
 * @property IniConfig config
 * @property CmsService cmsService
 * @property DataFormService dataFormService
 * @property DataTableFilterService dataTableFilterService
 * @property DataTableService dataTableService
 * @property DateTimeService dateTimeService
 * @property DbService dbService
 * @property ErrorService errorService
 * @property FileCacheService fileCacheService
 * @property FileHashService fileHashService
 * @property FilePermissionHelper filePermissionHelper
 * @property FilePermissionService filePermissionService
 * @property FileRemoveService fileRemoveService
 * @property FileResizeService fileResizeService
 * @property FileService fileService
 * @property FinderService finderService
 * @property FrontendHelper frontendHelper
 * @property FrontendService frontendService
 * @property FullPageService fullPageService
 * @property GeneratorService generatorService
 * @property ImageHandler imageHandler
 * @property JsonService jsonService
 * @property KeyValue $keyValue
 * @property LanguageService languageService
 * @property Logger $logger
 * @property MailService mailService
 * @property MenuService menuService
 * @property ModelService modelService
 * @property NamespaceService namespaceService
 * @property NestedSetService nestedSetService
 * @property NumberService numberService
 * @property PageContentService pageContentService
 * @property PageLanguageService pageLanguageService
 * @property PageRearrangeService pageRearrangeService
 * @property PageService pageService
 * @property PagesDataTableService pagesDataTableService
 * @property PaginateListService paginateListService
 * @property Permission $permission
 * @property PlaceholderService placeholderService
 * @property QueryService queryService
 * @property RearrangeService rearrangeService
 * @property ReCaptcha reCaptcha
 * @property RelationKeyService relationKeyService
 * @property RememberMeService rememberMeService
 * @property Routing routing
 * @property SecuritySingleToken securitySingleToken
 * @property Services services
 * @property StorageService storageService
 * @property StringService stringService
 * @property TableDataService tableDataService
 * @property TemplateService templateService
 * @property TinyMceService tinyMceService
 * @property TranslationService translationService
 * @property Translator translator
 * @property TwigService twigService
 * @property Url url
 * @property UrlService urlService
 * @property UserService userService
 * @property UserSettingsService userSettingsService
 * @property Validation $validation
 * @property VendorCleanUpService vendorCleanUpService
 * @property WebFormService webFormService
 * @property WebsiteSettingsBase websiteSettings
 */
class Injectable extends \Phalcon\Di\Injectable
{

}