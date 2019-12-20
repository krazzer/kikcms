<?php
declare(strict_types=1);

namespace Classes;

use Helpers\Unit;
use KikCMS\ObjectLists\CacheNodeMap;
use KikCMS\ObjectLists\CmsPluginList;
use KikCMS\ObjectLists\FieldMap;
use KikCMS\ObjectLists\FileMap;
use KikCMS\ObjectLists\FilePermissionList;
use KikCMS\ObjectLists\FullPageMap;
use KikCMS\ObjectLists\MenuGroupMap;
use KikCMS\ObjectLists\MenuItemMap;
use KikCMS\ObjectLists\PageLanguageList;
use KikCMS\ObjectLists\PageLanguageMap;
use KikCMS\ObjectLists\PageList;
use KikCMS\ObjectLists\PageMap;
use KikCMS\ObjectLists\PlaceholderFileThumbUrlMap;
use KikCMS\ObjectLists\PlaceholderFileUrlMap;
use KikCMS\ObjectLists\PlaceholderMap;
use KikCMS\ObjectLists\PlaceholderTable;
use KikCMS\ObjectLists\RememberMeHashList;
use KikCMS\ObjectLists\UserMap;
use KikCmsCore\Classes\ObjectList;
use StdClass;

class ObjectListTest extends Unit
{
    public function testObjectLists()
    {
        $objectLists = [
            CacheNodeMap::class,
            CmsPluginList::class,
            FieldMap::class,
            FileMap::class,
            FilePermissionList::class,
            FullPageMap::class,
            MenuGroupMap::class,
            MenuItemMap::class,
            PageLanguageList::class,
            PageLanguageMap::class,
            PageList::class,
            PageMap::class,
            PlaceholderFileThumbUrlMap::class,
            PlaceholderFileUrlMap::class,
            PlaceholderMap::class,
            PlaceholderTable::class,
            RememberMeHashList::class,
            UserMap::class,
        ];

        foreach ($objectLists as $objectListClassName){
            /** @var ObjectList $objectList */
            $objectList = new $objectListClassName([new StdClass()]);

            $objectList->getLast();
            $objectList->getFirst();
            $objectList->get(1);

            foreach ($objectList as $object){
                $this->assertInstanceOf(StdClass::class, $object);
            }

            $objectList->reverse();

        }
    }
}
