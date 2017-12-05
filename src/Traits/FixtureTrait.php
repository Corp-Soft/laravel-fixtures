<?php

namespace CorpSoft\Fixture\Traits;

use CorpSoft\Fixture\Exceptions\InvalidConfigException;
use CorpSoft\Fixture\Fixture;

/**
 * FixtureTrait provides functionality for loading, unloading and accessing fixtures for a test case.
 *
 * By using FixtureTrait, a test class will be able to specify which fixtures to load by overriding
 * the [[fixtures()]] method. It can then load and unload the fixtures using [[loadFixtures()]] and [[unloadFixtures()]].
 */
trait FixtureTrait
{
    /**
     * @var array the list of fixture objects available for the current test.
     * The array keys are the corresponding fixture class names.
     * The fixtures are listed in their dependency order. That is, fixture A is listed before B
     * if B depends on A.
     */
    private $_fixtures;

    /**
     * Declares the fixtures that are needed by the current test case.
     *
     * The return value of this method must be an array of fixture configurations. For example,
     *
     * ```php
     * [
     *     // anonymous fixture
     *     ArticleFixture::class,
     *     // "articles" fixture
     *     'articles' => ArticleFixture::class,
     * ]
     * ```
     *
     * @return array the fixtures needed by the current test case
     */
    public function fixtures(): array
    {
        return [];
    }

    /**
     * Loads the specified fixtures.
     * This method will call [[Fixture::load()]] for every fixture object.
     *
     * @param Fixture[] $fixtures the fixtures to be loaded. If this parameter is not specified,
     * the return value of [[getFixtures()]] will be used.
     *
     * @throws InvalidConfigException
     */
    public function loadFixtures($fixtures = null): void
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeLoad();
        }

        foreach ($fixtures as $fixture) {
            $fixture->load();
        }

        foreach (array_reverse($fixtures) as $fixture) {
            $fixture->afterLoad();
        }
    }

    /**
     * Unloads the specified fixtures.
     * This method will call [[Fixture::unload()]] for every fixture object.
     *
     * @param Fixture[] $fixtures the fixtures to be loaded. If this parameter is not specified,
     * the return value of [[getFixtures()]] will be used.
     *
     * @throws InvalidConfigException
     */
    public function unloadFixtures($fixtures = null): void
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeUnload();
        }

        $fixtures = array_reverse($fixtures);

        foreach ($fixtures as $fixture) {
            $fixture->unload();
        }

        foreach ($fixtures as $fixture) {
            $fixture->afterUnload();
        }
    }

    /**
     * Initialize the fixtures
     *
     * @throws InvalidConfigException
     */
    public function initFixtures(): void
    {
        $this->unloadFixtures();
        $this->loadFixtures();
    }

    /**
     * Returns the fixture objects as specified in [[globalFixtures()]] and [[fixtures()]].
     *
     * @throws InvalidConfigException
     *
     * @return Fixture[] the loaded fixtures for the current test case
     */
    public function getFixtures()
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures($this->fixtures());
        }

        return $this->_fixtures;
    }

    /**
     * Returns the named fixture.
     *
     * @param string $name the fixture name
     *
     * @throws InvalidConfigException
     *
     * @return Fixture the fixture object, or null if the named fixture does not exist
     */
    public function getFixture($name): Fixture
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures($this->fixtures());
        }

        $name = ltrim($name, '\\');

        return isset($this->_fixtures[$name]) ? $this->_fixtures[$name] : null;
    }

    /**
     * Creates the specified fixture instances.
     * All dependent fixtures will also be created.
     *
     * @param array $fixtures the fixtures to be created. You may provide fixture names or fixture configurations.
     * If this parameter is not provided, the fixtures specified in [[globalFixtures()]] and [[fixtures()]] will be created.
     *
     * @throws InvalidConfigException if fixtures are not properly configured or if a circular dependency among
     * the fixtures is detected
     *
     * @return Fixture[] the created fixture instances
     */
    protected function createFixtures(array $fixtures)
    {
        // normalize fixture configurations
        $config = [];  // configuration provided in test case
        $aliases = [];  // class name => alias or class name

        foreach ($fixtures as $name => $fixture) {
            if (!is_array($fixture)) {
                $class = ltrim($fixture, '\\');
                $fixtures[$name] = ['class' => $class];
                $aliases[$class] = is_int($name) ? $class : $name;
            } elseif (isset($fixture['class'])) {
                $class = ltrim($fixture['class'], '\\');
                $config[$class] = $fixture;
                $aliases[$class] = $name;
            } else {
                throw new InvalidConfigException("You must specify 'class' for the fixture '$name'.");
            }
        }

        // create fixture instances
        $instances = [];
        $stack = array_reverse($fixtures);

        while (($fixture = array_pop($stack)) !== null) {
            if ($fixture instanceof Fixture) {
                $class = get_class($fixture);
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                unset($instances[$name]);  // unset so that the fixture is added to the last in the next line
                $instances[$name] = $fixture;
            } else {
                $class = ltrim($fixture['class'], '\\');
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                if (!isset($instances[$name])) {
                    $instances[$name] = false;

                    $stack[] = $fixture = app()->make($fixture['class']);
                    foreach ($fixture->depends as $dep) {
                        // need to use the configuration provided in test case
                        $stack[] = isset($config[$dep]) ? $config[$dep] : ['class' => $dep];
                    }
                } elseif ($instances[$name] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            }
        }

        return $instances;
    }
}
