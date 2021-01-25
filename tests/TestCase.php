<?php

namespace Yoelpc4\LaravelMedia\Tests;

use CreateMediasTable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Yoelpc4\LaravelMedia\MediaServiceProvider;
use Yoelpc4\LaravelMedia\Tests\Models\User;
use Yoelpc4\LaravelMedia\Tests\Models\Post;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Post
     */
    protected $post;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->setUpFiles();

        $this->user = User::first();

        $this->post = Post::first();
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/../../laravel-media')
            ->loadEnvironmentFrom('.env.testing')
            ->bootstrapWith([
                LoadEnvironmentVariables::class,
            ]);

        $this->initDirectory($this->getStorageDirectory());

        $this->initDirectory($this->getAppDirectory());

        $app['config']->set('database.default', env('DB_CONNECTION'));
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.default', env('FILESYSTEM_DRIVER'));
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root'   => $this->getPublicDirectory(),
            'url'    => env('APP_URL').'/storage',
        ]);
    }

    /**
     * Set up test database
     *
     * @param  Application  $app
     * @return void
     */
    protected function setUpDatabase(Application $app)
    {
        include_once __DIR__.'/../database/migrations/create_medias_table.php.stub';

        (new CreateMediasTable)->up();

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        User::create([
            'name'  => 'John Doe',
            'email' => 'johndoe@mail.com',
        ]);

        $app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });

        Post::create([
            'title' => 'Foo Bar Baz',
            'body'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
        ]);
    }

    /**
     * Set up relation morph map
     *
     * @return void
     */
    protected function setUpMorphMap()
    {
        Relation::morphMap([
            'user' => User::class,
            'post' => Post::class,
        ]);
    }

    /**
     * Set up test files
     *
     * @return void
     */
    protected function setUpFiles()
    {
        $filesDirectory = $this->getFilesDirectory();

        $this->initDirectory($filesDirectory);

        File::copyDirectory(__DIR__.'/files', $this->getFilesDirectory());
    }

    /**
     * Init directory from the specified path
     *
     * @param  string  $path
     * @return void
     */
    protected function initDirectory(string $path)
    {
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        File::makeDirectory($path);
    }

    /**
     * Get storage directory path
     *
     * @param  string  $suffix
     * @return string
     */
    protected function getStorageDirectory($suffix = '')
    {
        return __DIR__.'/storage'.($suffix === '' ? '' : "/{$suffix}");
    }

    /**
     * Get app directory path
     *
     * @param  string  $suffix
     * @return string
     */
    protected function getAppDirectory($suffix = '')
    {
        return $this->getStorageDirectory().'/app'.($suffix === '' ? '' : "/{$suffix}");
    }

    /**
     * Get public directory path
     *
     * @param  string  $suffix
     * @return string
     */
    protected function getPublicDirectory($suffix = '')
    {
        return $this->getAppDirectory().'/public'.($suffix === '' ? '' : "/{$suffix}");
    }

    /**
     * Get files directory path
     *
     * @param  string  $suffix
     * @return string
     */
    protected function getFilesDirectory($suffix = '')
    {
        return $this->getAppDirectory().'/files'.($suffix === '' ? '' : "/{$suffix}");
    }
}
