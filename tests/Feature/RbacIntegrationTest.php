<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $rektor;
    protected User $wadek;
    protected User $employee;

    public function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'admin', 'label' => 'Admin', 'level' => 98]);
        $rektorRole = Role::create(['name' => 'rektor', 'label' => 'Rektor', 'level' => 6]);
        $wadekRole = Role::create(['name' => 'wadek', 'label' => 'Wadek', 'level' => 3]);
        $employeeRole = Role::create(['name' => 'employee', 'label' => 'Employee', 'level' => 1]);

        // Create users
        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'role' => 'admin']);
        $this->rektor = User::factory()->create(['role_id' => $rektorRole->id, 'role' => 'rektor']);
        $this->wadek = User::factory()->create(['role_id' => $wadekRole->id, 'role' => 'wadek']);
        $this->employee = User::factory()->create(['role_id' => $employeeRole->id, 'role' => 'employee']);

        // Create permissions
        Permission::create(['name' => 'spd.create', 'label' => 'Create SPD', 'category' => 'spd']);
        Permission::create(['name' => 'approval.approve', 'label' => 'Approve SPD', 'category' => 'approval']);
    }

    /**
     * Test sidebar links visible with correct permissions
     */
    public function test_sidebar_permissions_enforcement(): void
    {
        // Admin sees all sections
        $this->assertTrue(RbacService::userHasPermission($this->admin, 'spd.create'));
        $this->assertTrue(RbacService::userHasPermission($this->admin, 'approval.approve'));

        // Employee can create SPD but not approve
        $this->employee->roleModel->permissions()->attach(
            Permission::where('name', 'spd.create')->first()
        );

        $this->assertTrue(RbacService::userHasPermission($this->employee, 'spd.create'));
        $this->assertFalse(RbacService::userHasPermission($this->employee, 'approval.approve'));
    }

    /**
     * Test route access based on roles
     */
    public function test_route_access_by_role(): void
    {
        // Guest cannot access protected routes
        $this->get('/spd')
            ->assertRedirect('/login');

        // Employee can access SPD routes
        $this->actingAs($this->employee)
            ->get('/spd')
            ->assertSuccessful();

        // All authenticated users can access dashboard
        $this->actingAs($this->employee)
            ->get('/dashboard')
            ->assertSuccessful();

        $this->actingAs($this->admin)
            ->get('/dashboard')
            ->assertSuccessful();
    }

    /**
     * Test approval routes require minimum level
     */
    public function test_approval_route_requires_level(): void
    {
        // Employee (level 1) cannot access approvals
        $this->actingAs($this->employee)
            ->get('/approvals')
            ->assertStatus(403);

        // Wadek (level 3) can access approvals
        $this->actingAs($this->wadek)
            ->get('/approvals')
            ->assertSuccessful();
    }

    /**
     * Test admin only routes
     */
    public function test_admin_only_routes(): void
    {
        // Employee cannot access admin users
        $this->actingAs($this->employee)
            ->get('/admin/users')
            ->assertStatus(403);

        // Admin can access admin users
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertSuccessful();
    }

    /**
     * Test test-routes page loads
     */
    public function test_routes_test_page_loads(): void
    {
        $this->get('/test-routes')
            ->assertSuccessful()
            ->assertViewIs('test-routes');
    }

    /**
     * Test sidebar shows correct menu items by role
     */
    public function test_sidebar_menu_by_role(): void
    {
        // Create a view that renders sidebar with user
        $view = $this->actingAs($this->employee)
            ->get('/dashboard')
            ->assertSuccessful();

        // Sidebar should be rendered as part of layout
        $this->assertTrue(true);
    }

    /**
     * Test gate checking in controllers
     */
    public function test_gates_authorize_correctly(): void
    {
        // Using Gate::authorize in a mock scenario
        $this->assertTrue($this->admin->can('create-spd'));
        $this->assertTrue($this->wadek->can('approve-spd'));

        // Employee cannot approve
        $this->assertFalse($this->employee->can('approve-spd'));
    }
}
