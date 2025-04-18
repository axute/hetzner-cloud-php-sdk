<?php

namespace LKDev\Tests\Unit\Models\PrimaryIps;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\PrimaryIps\PrimaryIps;
use LKDev\Tests\TestCase;

class PrimaryIPsTest extends TestCase
{
    protected PrimaryIps $primaryIps;

    public function setUp(): void
    {
        parent::setUp();
        $this->primaryIps = new PrimaryIps($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIP.json')));
        $primaryIP = $this->primaryIps->get(1);
        $this->assertEquals(4711, $primaryIP->id);
        $this->assertEquals('my-resource', $primaryIP->name);
        $this->assertLastRequestEquals('GET', '/primary_ips/1');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIPs.json')));
        $primaryIP = $this->primaryIps->getByName('my-resource');
        $this->assertEquals(4711, $primaryIP->id);
        $this->assertEquals('my-resource', $primaryIP->name);

        $this->assertLastRequestQueryParametersContains('name', 'my-resource');
        $this->assertLastRequestEquals('GET', '/primary_ips');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIPs.json')));
        $primaryIPs = $this->primaryIps->all();

        $this->assertCount(1, $primaryIPs);
        $this->assertEquals(4711, $primaryIPs[0]->id);
        $this->assertEquals('my-resource', $primaryIPs[0]->name);
        $this->assertLastRequestEquals('GET', '/primary_ips');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIPs.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $primaryIPs = $this->primaryIps->list()->primary_ips;

        $this->assertCount(1, $primaryIPs);
        $this->assertEquals(4711, $primaryIPs[0]->id);
        $this->assertEquals('my-resource', $primaryIPs[0]->name);
        $this->assertLastRequestEquals('GET', '/primary_ips');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testCreateWithName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIP.json')));
        $primaryIp = $this->primaryIps->create(
            'ipv4', 'Web Frontend', 'server'
        );

        $this->assertEquals(4711, $primaryIp->id);
        $this->assertEquals('my-resource', $primaryIp->name);
        $this->assertLastRequestEquals('POST', '/primary_ips');
        $this->assertLastRequestBodyParametersEqual(['type' => 'ipv4', 'name' => 'Web Frontend', 'assignee_type' => 'server']);
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testDelete()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/primaryIP.json')));
        $floatingIp = $this->primaryIps->get(4711);
        $this->assertLastRequestEquals('GET', '/primary_ips/4711');

        $this->mockHandler->append(new Response(204, []));
        $this->assertTrue($floatingIp->delete());
        $this->assertLastRequestEquals('DELETE', '/primary_ips/4711');
    }
}
