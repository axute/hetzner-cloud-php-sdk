<?php

namespace LKDev\Tests\Unit\Models\LoadBalancers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\LoadBalancers\LoadBalancers;
use LKDev\Tests\TestCase;

class LoadBalancersTest extends TestCase
{
    protected LoadBalancers $loadBalancers;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadBalancers = new LoadBalancers($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancers.json')));
        $loadBalancers = $this->loadBalancers->all();

        $this->assertCount(1, $loadBalancers);
        $this->assertLastRequestEquals('GET', '/load_balancers');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancer.json')));
        $loadBalancer = $this->loadBalancers->get(4711);

        $this->assertEquals(4711, $loadBalancer->id);
        $this->assertEquals('my-resource', $loadBalancer->name);

        $this->assertLastRequestEquals('GET', '/load_balancers/4711');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancers.json')));
        $loadBalancer = $this->loadBalancers->getByName('my-resource');

        $this->assertEquals(4711, $loadBalancer->id);
        $this->assertEquals('my-resource', $loadBalancer->name);
        $this->assertLastRequestEquals('GET', '/load_balancers');
        $this->assertLastRequestQueryParametersContains('name', 'my-resource');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancers.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $loadBalancers = $this->loadBalancers->list()->load_balancers;

        $this->assertCount(1, $loadBalancers);
        $this->assertEquals(4711, $loadBalancers[0]->id);
        $this->assertEquals('my-resource', $loadBalancers[0]->name);
        $this->assertLastRequestEquals('GET', '/load_balancers');
    }
}
