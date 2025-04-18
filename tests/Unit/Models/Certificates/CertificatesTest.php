<?php

/**
 * Created by PhpStorm.
 * User: lukaskammerling
 * Date: 11.07.18
 * Time: 18:31.
 */

namespace LKDev\Tests\Unit\Models\Certificates;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use LKDev\HetznerCloud\APIException;
use LKDev\HetznerCloud\Models\Certificates\Certificates;
use LKDev\Tests\TestCase;

class CertificatesTest extends TestCase
{
    protected Certificates $certificates;

    public function setUp(): void
    {
        parent::setUp();
        $this->certificates = new Certificates($this->hetznerApi->getHttpClient());
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGet()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificate.json')));
        $certificate = $this->certificates->get(897);
        $this->assertEquals(897, $certificate->id);
        $this->assertEquals('my website cert', $certificate->name);
        $this->assertEquals("-----BEGIN CERTIFICATE-----\n...", $certificate->certificate);

        $this->assertLastRequestEquals('GET', '/certificates/897');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testGetByName()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificates.json')));
        $certificate = $this->certificates->getByName('my website cert');
        $this->assertEquals(897, $certificate->id);
        $this->assertEquals('my website cert', $certificate->name);
        $this->assertEquals("-----BEGIN CERTIFICATE-----\n...", $certificate->certificate);

        $this->assertLastRequestEquals('GET', '/certificates');
        $this->assertLastRequestQueryParametersContains('name', 'my website cert');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testAll()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificates.json')));
        $certificates = $this->certificates->all();

        $this->assertCount(1, $certificates);
        $this->assertEquals(897, $certificates[0]->id);
        $this->assertEquals('my website cert', $certificates[0]->name);
        $this->assertEquals("-----BEGIN CERTIFICATE-----\n...", $certificates[0]->certificate);

        $this->assertLastRequestEquals('GET', '/certificates');
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testList()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificates.json')));
        /** @noinspection PhpUndefinedFieldInspection */
        $certificates = $this->certificates->list()->certificates;

        $this->assertCount(1, $certificates);
        $this->assertEquals(897, $certificates[0]->id);
        $this->assertEquals('my website cert', $certificates[0]->name);
        $this->assertEquals("-----BEGIN CERTIFICATE-----\n...", $certificates[0]->certificate);

        $this->assertLastRequestEquals('GET', '/certificates');
    }

    /**
     * @throws GuzzleException
     * @throws APIException
     */
    public function testCreate()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificate.json')));

        $this->certificates->create('my cert', "-----BEGIN CERTIFICATE-----\n...", "-----BEGIN PRIVATE KEY-----\n...");

        $this->assertLastRequestEquals('POST', '/certificates');
        $this->assertLastRequestBodyParametersEqual(['name' => 'my cert', 'certificate' => "-----BEGIN CERTIFICATE-----\n...", 'private_key' => "-----BEGIN PRIVATE KEY-----\n..."]);
    }

    /**
     * @throws APIException
     * @throws GuzzleException
     */
    public function testDelete()
    {
        $this->mockHandler->append(new Response(200, [], file_get_contents(__DIR__.'/fixtures/certificate.json')));

        $certificate = $this->certificates->get(897);
        $this->mockHandler->append(new Response(204, []));
        $this->assertTrue($certificate->delete());
        $this->assertLastRequestEquals('DELETE', '/certificates/897');
    }
}
