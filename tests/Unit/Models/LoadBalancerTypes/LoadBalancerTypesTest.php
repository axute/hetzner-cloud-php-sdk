<?php

namespace LKDev\Tests\Unit\Models\LoadBalancerTypes;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\LoadBalancerTypes\LoadBalancerTypes;
use LKDev\Tests\TestCase;

class LoadBalancerTypesTest extends TestCase
{
    protected LoadBalancerTypes $load_balancer_types;

    public function setUp(): void
    {
        parent::setUp();
        $this->load_balancer_types = new LoadBalancerTypes($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancerTypes.json')));
        $loadBalancerTypes = $this->load_balancer_types->all();

        $this->assertCount(2, $loadBalancerTypes);
        $this->assertLastRequestEquals('GET', '/load_balancer_types');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancerType.json')));
        $loadBalancer = $this->load_balancer_types->get(4711);

        $this->assertEquals(4711, $loadBalancer->id);
        $this->assertEquals('lb11', $loadBalancer->name);

        $this->assertLastRequestEquals('GET', '/load_balancer_types/4711');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancerTypes.json')));
        $loadBalancer = $this->load_balancer_types->getByName('lb11');

        $this->assertEquals(4711, $loadBalancer->id);
        $this->assertEquals('lb11', $loadBalancer->name);
        $this->assertLastRequestEquals('GET', '/load_balancer_types');
        $this->assertLastRequestQueryParametersContains('name', 'lb11');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/loadBalancerTypes.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $loadBalancerTypes = $this->load_balancer_types->list()->load_balancer_types;

        $this->assertCount(2, $loadBalancerTypes);
        $this->assertEquals(4711, $loadBalancerTypes[0]->id);
        $this->assertEquals('lb11', $loadBalancerTypes[0]->name);
        $this->assertLastRequestEquals('GET', '/load_balancer_types');
    }
}
