<?php

namespace CorpSoft\Fixture;

/**
 * Fixture represents a fixed state of a test environment.
 */
class Fixture
{
    /**
     * @var array the fixtures that this fixture depends on. This must be a list of the dependent
     * fixture class names.
     */
    public $depends = [];

    /**
     * Loads the fixture.
     * This method is called before performing every test method.
     * You should override this method with concrete implementation about how to set up the fixture.
     */
    public function load(): void
    {
    }

    /**
     * This method is called BEFORE any fixture data is loaded for the current test.
     */
    public function beforeLoad(): void
    {
    }

    /**
     * This method is called AFTER all fixture data have been loaded for the current test.
     */
    public function afterLoad(): void
    {
    }

    /**
     * Unloads the fixture.
     * This method is called after every test method finishes.
     * You may override this method to perform necessary cleanup work for the fixture.
     */
    public function unload(): void
    {
    }

    /**
     * This method is called BEFORE any fixture data is unloaded for the current test.
     */
    public function beforeUnload(): void
    {
    }

    /**
     * This method is called AFTER all fixture data have been unloaded for the current test.
     */
    public function afterUnload(): void
    {
    }
}
