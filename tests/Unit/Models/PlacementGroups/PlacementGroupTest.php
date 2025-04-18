<?php

namespace LKDev\Tests\Unit\Models\PlacementGroups;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroup;
use LKDev\HetznerCloud\Models\PlacementGroups\PlacementGroups;
use LKDev\Tests\TestCase;

class PlacementGroupTest extends TestCase
{
    protected PlacementGroup $placement_group;

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function setUp(): void
    {
        parent::setUp();
        $tmp = new PlacementGroups($this->hetznerApi->getHttpClient());
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/placement_group.json')));
        $this->placement_group = $tmp->get(4862);
    }
}
