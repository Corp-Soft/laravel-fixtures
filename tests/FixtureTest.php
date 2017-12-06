<?php

namespace CorpSoft\Tests\Fixture;

use CorpSoft\Fixture\Exceptions\InvalidConfigException;

/**
 * Class FixtureTest
 *
 * @package CorpSoft\Tests\Fixture
 */
class FixtureTest extends TestCase
{
    public function testLoadFixture()
    {
        $fixture = new UserFixture();
        $fixture->load();

        $this->assertDatabaseHas('users', [
            'email' => 'user1@example.org',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user2@example.org',
        ]);

        $this->assertCount(2, $fixture);
    }

    public function testUnloadFixture()
    {
        $fixture = new UserFixture();
        $fixture->load();

        $this->assertDatabaseHas('users', [
            'email' => 'user1@example.org',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user2@example.org',
        ]);

        $this->assertCount(2, $fixture);

        $fixture->unload();

        $this->assertDatabaseMissing('users', [
            'email' => 'user1@example.org',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'user2@example.org',
        ]);

        $this->assertCount(0, $fixture);
    }

    public function testGetFixtureData()
    {
        $fixture = new UserFixture();
        $fixture->load();

        $this->assertCount(2, $fixture->data);
        $this->assertArraySubset([
            ['email' => 'user1@example.org'],
        ], $fixture->data);
    }

    public function testLoadNotExistingDataFile()
    {
        $this->expectException(InvalidConfigException::class);

        $fixture = new UserFixture();
        $fixture->dataFile = 'user.php';
        $fixture->load();
    }
}
