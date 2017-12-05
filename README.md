<p align="center">
    <img src="https://avatars1.githubusercontent.com/u/33844443" height="100px">
    <h1 align="center">Laravel Fixtures Package</h1>
    <br>
</p>

Fixtures are used to load a "fake" set of data into a database that can then be used for testing or to help give you some interesting data while you're developing your application. 

A fixture may depend on other fixtures, specified via its `CorpSoft\Fixture\Fixture::$depends` property. When a fixture is being loaded, the fixtures it depends on will be automatically loaded BEFORE the fixture; and when the fixture is being unloaded, the dependent fixtures will be unloaded AFTER the fixture.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist corp-soft/laravel-fixtures "*"
```

or add

```
"corp-soft/laravel-fixtures": "*"
```

to the require section of your `composer.json` file.


### Defining a Fixture

To define a fixture, create a new class by extending `CorpSoft\Fixture\ActiveFixture`.

The following code defines a fixture about the `User` Illuminate\Database\Eloquent\Model and the corresponding users table.

```php
<?php

namespace Fixtures;

use CorpSoft\Fixture\ActiveFixture;
use App\Models\User;

class UserFixture extends ActiveFixture
{
    /**
     * @var string
     */
    public $dataFile = 'fixtures/users.php';

    /**
     * @var string
     */
    public $modelClass = User::class;
}
``` 

> Tip: Each ActiveFixture is about preparing a DB table for testing purpose. You may specify the table by setting either the CorpSoft\Fixture\ActiveFixture::$table property or the CorpSoft\Fixture\ActiveFixture::$modelClass property. If the latter, the table name will be taken from the Illuminate\Database\Eloquent\Model class specified by modelClass.

The fixture data for an `ActiveFixture` fixture is usually provided in a file located at `public/storage/fixtures/table_name.php`.
The data file should return an array of data rows to be inserted into the user table. For example:

```php
<?php
// public/storage/fixtures/users.php
return [
    [
        'name' => 'user1',
        'email' => 'user1@example.org',
        'password' => bcrypt('secret'),
    ],
    [
        'name' => 'user2',
        'email' => 'user2@example.org',
        'password' => bcrypt('secret'),
    ],
];
```

As we described earlier, a fixture may depend on other fixtures. For example, a `UserProfileFixture` may need to depends on `UserFixture` because the user profile table contains a foreign key pointing to the user table. The dependency is specified via the `CorpSoft\Fixture\Fixture::$depends` property, like the following:

```php
<?php

namespace Fixtures;

use CorpSoft\Fixture\ActiveFixture;
use App\Models\UserProfile;

class UserProfileFixture extends ActiveFixture
{
    /**
     * @var string
     */
    public $dataFile = 'fixtures/user_profile.php';

    /**
     * @var string
     */
    public $modelClass = UserProfile::class;

    /**
     * @var array
     */
    public $depends = [UserFixture::class];
}
```

The dependency also ensures, that the fixtures are loaded and unloaded in a well defined order. In the above example `UserFixture` will always be loaded before `UserProfileFixture` to ensure all foreign key references exist and will be unloaded after `UserProfileFixture` has been unloaded for the same reason.

### Using Fixtures

1) If you are using `phpunit` to test your code, then you need to add `CorpSoft\Fixture\Traits\FixtureTrait` to abstract class `TestCase` in the `tests` folder as follows:
```php
<?php

namespace Tests;

use CorpSoft\Fixture\Traits\FixtureTrait;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, FixtureTrait;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->initFixtures();
    }
}
```
2) If you are using `Laravel Dusk` to test your code, then you need to add `CorpSoft\Fixture\Traits\FixtureTrait` to abstract class `DuskTestCase` in the `tests` folder as follows:

```php
<?php

namespace Tests;

use CorpSoft\Fixture\Traits\FixtureTrait;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication, FixtureTrait;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->initFixtures();
    }
    
    // other methods
}
```

After this steps you can define fixtures in your test classes as follows:
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use Fixtures\UserProfileFixture;

class ExampleTest extends TestCase
{
    /**
     * Declares the fixtures that are needed by the current test case.
     *
     * @return array the fixtures needed by the current test case
     */
    public function fixtures(): array
    {
        return [
            'profiles' => UserProfileFixture::class,
        ];
    }
        
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $this->assertTrue(true);
    }
}
```

The fixtures listed in the `fixtures()` method will be automatically loaded before a test is executed.
And as we described before, when a fixture is being loaded, all its dependent fixtures will be automatically loaded first. In the above example, because `UserProfileFixture` depends on `UserFixture`, when running any test method in the test class, two fixtures will be loaded sequentially: `UserFixture` and `UserProfileFixture`.
