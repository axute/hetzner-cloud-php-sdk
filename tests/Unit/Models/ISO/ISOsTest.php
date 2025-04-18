<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\ISO;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\ISOs\ISOs;
use LKDev\Tests\TestCase;

class ISOsTest extends TestCase
{
    protected ISOs $isos;

    public function setUp(): void
    {
        parent::setUp();
        $this->isos = new ISOs($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/iso.json')));

        $iso = $this->isos->get(4711);
        $this->assertEquals(4711, $iso->id);
        $this->assertEquals('FreeBSD-11.0-RELEASE-amd64-dvd1', $iso->name);

        $this->assertLastRequestEquals('GET', '/isos/4711');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/isos.json')));

        $isos = $this->isos->all();

        $this->assertCount(1, $isos);
        $this->assertEquals(4711, $isos[0]->id);
        $this->assertEquals('FreeBSD-11.0-RELEASE-amd64-dvd1', $isos[0]->name);

        $this->assertLastRequestEquals('GET', '/isos');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/isos.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $isos = $this->isos->list()->isos;

        $this->assertCount(1, $isos);
        $this->assertEquals(4711, $isos[0]->id);
        $this->assertEquals('FreeBSD-11.0-RELEASE-amd64-dvd1', $isos[0]->name);

        $this->assertLastRequestEquals('GET', '/isos');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/isos.json')));
        $iso = $this->isos->getByName('FreeBSD-11.0-RELEASE-amd64-dvd1');
        $this->assertEquals(4711, $iso->id);
        $this->assertEquals('FreeBSD-11.0-RELEASE-amd64-dvd1', $iso->name);
        $this->assertLastRequestQueryParametersContains('name', 'FreeBSD-11.0-RELEASE-amd64-dvd1');
        $this->assertLastRequestEquals('GET', '/isos');
    }
}
