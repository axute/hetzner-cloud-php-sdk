<?php

namespace LKDev\Tests\Unit\Models\PlacementGroups;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\APIResponse;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroup;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroups;
use LKDev\HetznerCloud\Models\Servers\ServerReference;
use LKDev\Tests\TestCase;

/**
 * Class NetworksTest.
 */
class PlacementGroupsTest extends TestCase
{
    protected PlacementGroups $placement_groups;

    public function setUp(): void
    {
        parent::setUp();
        $this->placement_groups = new PlacementGroups($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_groups.json')));
        $placement_groups = $this->placement_groups->all();
        $this->assertCount(1, $placement_groups);

        $this->assertLastRequestEquals('GET', '/placement_groups');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_groups.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $placement_groups = $this->placement_groups->list()->placement_groups;
        $this->assertCount(1, $placement_groups);
        $this->assertLastRequestEquals('GET', '/placement_groups');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_groups.json')));
        $placement_group = $this->placement_groups->getByName('my_placemengroup');
        $this->assertEquals(4862, $placement_group->id);
        $this->assertEquals('my_placemengroup', $placement_group->name);

        $this->assertCount(1, $placement_group->servers);
        $this->assertInstanceOf(ServerReference::class, $placement_group->servers[0]);

        $this->assertEmpty($placement_group->labels);

        $this->assertLastRequestEquals('GET', '/placement_groups');
    }

    /**
     * @throws APIException|GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_group.json')));
        $placement_group = $this->placement_groups->get(4862);

        $this->assertEquals(4862, $placement_group->id);
        $this->assertEquals('my_placemengroup', $placement_group->name);

        $this->assertCount(1, $placement_group->servers);
        $this->assertInstanceOf(ServerReference::class, $placement_group->servers[0]);

        $this->assertEmpty($placement_group->labels);

        $this->assertLastRequestEquals('GET', '/placement_groups/4862');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testBasicCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_group.json')));
        $resp = $this->placement_groups->create('my_placemengroup', 'spread');
        $this->assertInstanceOf(APIResponse::class, $resp);
        $this->assertInstanceOf(PlacementGroup::class, $resp->getResponsePart('placement_group'));

        $this->assertLastRequestEquals('POST', '/placement_groups');
        $this->assertLastRequestBodyParametersEqual(['name' => 'my_placemengroup', 'type' => 'spread']);
    }
}
