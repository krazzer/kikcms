<?php

namespace KikCMS\Services\Pages;


use PHPUnit\Framework\TestCase;

class UrlServiceTest extends TestCase
{
    public function testToSlug()
    {
        $urlService = new UrlService();

        $allAsciiSymbols = '!"#$%&\\\'()*+,./:;<=>?@ÇüéâäàåçêëèïîìÄÅÉæÆôöòûùÿÖÜø£Ø×ƒáíóúñÑªº¿®¬½¼¡«»░▒▓│┤ÁÂÀ©╣║╗╝¢¥┐└┴┬├─┼ãÃ╚╔╩╦╠═╬¤ðÐÊËÈıÍÎÏ┘┌█▄¦Ì▀ÓßÔÒõÕµþÞÚÛÙýÝ¯´≡±‗¾¶§÷¸°¨·¹³²■';

        $this->assertEquals('test', $urlService->toSlug('test'));
        $this->assertEquals('cueaaaaceeeiiiaaeaeaeooouuyouo-o-faiounnao-aaa-aa-ddeeeiiii-i-ossoooouththuuuyy', $urlService->toSlug($allAsciiSymbols));
        $this->assertEquals('', $urlService->toSlug('😀😁😂🤣'));
        $this->assertEquals('test-test', $urlService->toSlug('-----tEsT--------TesT-----'));
        $this->assertEquals('hello-this-a-sentence', $urlService->toSlug('Hello this a sentence'));
    }
}
