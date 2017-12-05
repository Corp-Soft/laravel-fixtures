<?php

namespace CorpSoft\Fixture;

use ArrayAccess;
use CorpSoft\Fixture\Exceptions\InvalidConfigException;
use CorpSoft\Fixture\Traits\ArrayAccessTrait;
use Countable;
use IteratorAggregate;

/**
 * BaseActiveFixture is the base class for fixture classes that support accessing fixture data as ActiveRecord objects.
 */
abstract class BaseActiveFixture extends Fixture implements IteratorAggregate, ArrayAccess, Countable
{
    use ArrayAccessTrait;

    /**
     * @var string the AR model class associated with this fixture
     */
    public $modelClass;

    /**
     * @var array the data rows. Each array element represents one row of data (column name => column value).
     */
    public $data = [];

    /**
     * @var string the file path that contains the fixture data to be returned by [[getData()]]
     */
    public $dataFile;

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        $this->data = $this->getData();
    }

    /**
     * @inheritdoc
     */
    public function unload(): void
    {
        $this->data = [];
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return the data array that will be stored in [[data]] after inserting into the database.
     *
     * @throws InvalidConfigException if the specified data file does not exist
     *
     * @return array the data to be put into the database
     */
    protected function getData(): array
    {
        if ($this->dataFile === false || $this->dataFile === null) {
            return [];
        }

        $dataFile = $this->resolveDataFilePath();

        if (is_file($dataFile)) {
            return require($dataFile);
        } else {
            throw new InvalidConfigException("Fixture data file does not exist: {$this->dataFile}");
        }
    }

    /**
     * Returns the fixture data file path.
     *
     * @return string
     */
    protected function resolveDataFilePath(): string
    {
        return storage_path($this->dataFile);
    }
}
