<?php

namespace CorpSoft\Tests\Fixture;

use CorpSoft\Fixture\ActiveFixture;

class UserFixture extends ActiveFixture
{
    /**
     * @var string
     */
    public $dataFile = 'users.php';

    /**
     * @var string
     */
    public $modelClass = User::class;

    /**
     * Returns the fixture data file path.
     *
     * @return string
     */
    protected function resolveDataFilePath(): string
    {
        return __DIR__ . '/resources/' . $this->dataFile;
    }
}
