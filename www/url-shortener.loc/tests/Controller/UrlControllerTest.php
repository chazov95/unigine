<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UrlControllerTest extends WebTestCase
{
    public function testEncodeUrlSuccess()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/encode-url',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'http://example.com'])
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('hash', $response);
        $this->assertNotEmpty($response['hash']);
    }

    public function testEncodeUrlNoUrlProvided()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/encode-url',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([])
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('URL is required', $response['error']);
    }

    public function testEncodeUrlInvalidUrl()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/encode-url',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'invalid-url'])
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('errors', $response);
        $this->assertContains('This value is not a valid URL.', $response['errors']);
    }

    public function testEncodeUrlAlreadyExists()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/encode-url',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'http://example.com'])
        );

        $client->request(
            'POST',
            '/api/encode-url',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['url' => 'http://example.com'])
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('hash', $response);
    }
}
