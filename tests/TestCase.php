<?php

namespace Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Workbench\App\Models\Site;
use Workbench\App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    protected ?User $superAdmin = null;

    protected ?User $user = null;

    protected ?Site $site = null;

    protected function setUp(): void
    {
        // Always show errors when testing
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        parent::setUp();

        // Disable Scout during tests to prevent indexing operations
        // This speeds up tests and avoids external dependencies on search services
        config(['scout.driver' => null]);

        $this->withoutVite();
    }

    /**
     * Run database migrations
     */
    protected function migrate(): self
    {
        $this->artisan('migrate');

        return $this;
    }

    /**
     * Set up default "super admin" user
     */
    protected function setUpSuperAdmin(): self
    {
        // Create the site first
        $this->site = Site::factory()->create();

        // Create the super admin user
        $this->superAdmin = User::factory()->make();
        $this->superAdmin->assignRole('super_admin')->save();

        // Act as the super admin
        $this->actingAs($this->superAdmin);

        // Set the site as the current tenant
        if ($this->site) {
            app()->instance('current_site', $this->site);
        }

        return $this;
    }

    /**
     * Set up a common user with no roles or permissions
     */
    protected function setUpCommonUser(): self
    {
        $this->user = User::factory()->create();

        $this->actingAs($this->user);

        return $this;
    }

    public function ignorePackageDiscoveriesFrom()
    {
        return [
            // A list of packages that should not be auto-discovered when running tests
            'laravel/telescope',
        ];
    }
}
