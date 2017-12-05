<?php

namespace CorpSoft\Fixture;

use CorpSoft\Fixture\Exceptions\InvalidConfigException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|Illuminate\Database\Eloquent\Model class]].
 *
 * [[modelClass]] must be set. You should also provide fixture data in the file
 * specified by [[dataFile]] or overriding [[getData()]] if you want to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[resetTable()]] to remove any existing data in the table.
 * It will then populate the table with the data returned by [[getData()]].
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property.
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var string the file path that contains the fixture data to be returned by [[getData()]]
     */
    public $dataFile;

    /**
     * @var string table name
     */
    protected $table;

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        $this->data = [];
        $table = $this->getTable();

        foreach ($this->getData() as $alias => $row) {
            $primaryKey = DB::table($table)->insertGetId($row);
            $this->data[$alias] = array_merge($row, ['id' => $primaryKey]);
        }
    }

    /**
     * @inheritdoc
     */
    public function unload(): void
    {
        $this->resetTable();

        parent::unload();
    }

    /**
     * Removes all existing data from the specified table.
     *
     * @throws Exception
     */
    protected function resetTable(): void
    {
        DB::table($this->getTable())->delete();
    }

    /**
     * Returns the table name for [[modelClass]]
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getTable(): string
    {
        if ($this->modelClass === null && $this->table === null) {
            throw new InvalidConfigException('Either "modelClass" or "table" must be set.');
        }

        if ($this->table === null) {
            $this->table = with(new $this->modelClass())->getTable();
        }

        if (!Schema::hasTable($this->table)) {
            throw new InvalidConfigException("Table does not exist: {$this->table}");
        }

        return $this->table;
    }
}
