<?php
declare(strict_types=1);

namespace Services\Cms;

use DateInterval;
use DateTime;
use Helpers\Unit;
use KikCMS\Classes\Phalcon\IniConfig;
use KikCMS\Models\User;
use KikCMS\ObjectLists\RememberMeHashList;
use KikCMS\Objects\RememberMeHash;
use KikCMS\Services\Cms\RememberMeService;
use Phalcon\Http\Cookie;
use Phalcon\Http\Response\Cookies;
use Phalcon\Security;

class RememberMeServiceTest extends Unit
{
    public function testGetUserIdByCookie()
    {
        $rememberMeService = new RememberMeService();
        $rememberMeService->setDI($this->getDbDi());

        $cookie = new Cookie('rememberMeToken', '1.token');

        $cookieMock = $this->createMock(Cookie::class);
        $cookieMock->method('getValue')->willReturn(null);

        $cookies = $this->createMock(Cookies::class);
        $cookies->method('get')->willReturn($cookieMock);

        $config = $this->createMock(IniConfig::class);
        $config->method('isDev')->willReturn(false);

        $rememberMeService->cookies = $cookies;
        $rememberMeService->config  = $config;

        // no cookie
        $this->assertEquals(null, $rememberMeService->getUserIdByCookie());

        $cookies = $this->createMock(Cookies::class);
        $cookies->method('get')->willReturn($cookie);

        $rememberMeService->cookies = $cookies;

        // no user
        $this->assertEquals(null, $rememberMeService->getUserIdByCookie());

        $user = $this->createAndSaveTestUser();

        // no hashlist
        $this->assertEquals(null, $rememberMeService->getUserIdByCookie());

        // invalid hash
        $this->createAndSaveHashList($rememberMeService->security, $user, false, '1.tokenINVALID');
        $this->assertEquals(null, $rememberMeService->getUserIdByCookie());

        // expired
        $this->createAndSaveHashList($rememberMeService->security, $user, true, '1.token');
        $this->assertEquals(null, $rememberMeService->getUserIdByCookie());

        // valid
        $this->createAndSaveHashList($rememberMeService->security, $user, false, '1.token');
        $this->assertEquals(1, $rememberMeService->getUserIdByCookie());
    }

    /**
     * @param Security $security
     * @param User $user
     * @param bool $expired
     * @param string $cookieToken
     */
    private function createAndSaveHashList(Security $security, User $user, bool $expired, string $cookieToken)
    {
        if ($expired) {
            $expire = (new DateTime)->sub(new DateInterval('P30D'));
        } else {
            $expire = (new DateTime)->add(new DateInterval('P30D'));
        }

        $hashList = new RememberMeHashList;
        $hashList->add(new RememberMeHash($expire, $security->hash($cookieToken)));

        $user->setRememberMe(serialize($hashList));
        $user->save();
    }
}
