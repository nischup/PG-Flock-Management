<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_log_index_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/audit-log')
            ->assertStatus(200);
    }

    public function test_audit_log_service_logs_created_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $testUser = User::factory()->create();

        AuditLogService::logCreated($testUser);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $testUser->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_service_logs_updated_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Original Name']);
        $oldValues = $testUser->getAttributes();

        $testUser->update(['name' => 'Updated Name']);

        AuditLogService::logUpdated($testUser, $oldValues);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $testUser->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_service_logs_deleted_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $oldValues = $testUser->getAttributes();

        AuditLogService::logDeleted($testUser);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'deleted',
            'auditable_type' => User::class,
            'auditable_id' => $testUser->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_audit_log_service_logs_login_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLogService::logLogin();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'login',
            'auditable_type' => 'User',
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'tags' => 'authentication',
        ]);
    }

    public function test_audit_log_service_logs_logout_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLogService::logLogout();

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'logout',
            'auditable_type' => 'User',
            'auditable_id' => $user->id,
            'user_id' => $user->id,
            'tags' => 'authentication',
        ]);
    }

    public function test_audit_log_service_logs_custom_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        AuditLogService::logCustom(
            'custom_action',
            User::class,
            123,
            ['custom_data' => 'test'],
            'custom_tag'
        );

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'custom_action',
            'auditable_type' => User::class,
            'auditable_id' => 123,
            'user_id' => $user->id,
            'tags' => 'custom_tag',
        ]);
    }

    public function test_audit_log_controller_returns_analysis_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create some test audit logs
        AuditLog::factory()->count(5)->create([
            'event' => 'created',
            'auditable_type' => User::class,
            'user_id' => $user->id,
        ]);

        $response = $this->get('/audit-log');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('auditLogs')
            ->has('analysis')
            ->has('filters')
        );
    }

    public function test_audit_log_controller_filters_by_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create audit logs with different events
        AuditLog::factory()->create(['event' => 'created']);
        AuditLog::factory()->create(['event' => 'updated']);

        $response = $this->get('/audit-log?event=created');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('auditLogs.data', function ($data) {
            return count($data) === 1 && $data[0]['event'] === 'created';
        })
        );
    }

    public function test_audit_log_controller_filters_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $this->actingAs($user1);

        // Create audit logs for different users
        AuditLog::factory()->create(['user_id' => $user1->id]);
        AuditLog::factory()->create(['user_id' => $user2->id]);

        $response = $this->get("/audit-log?user_id={$user1->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('auditLogs.data', function ($data) use ($user1) {
            return count($data) === 1 && $data[0]['user_id'] === $user1->id;
        })
        );
    }

    public function test_audit_log_controller_filters_by_date_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create audit logs with different dates
        AuditLog::factory()->create([
            'created_at' => now()->subDays(5),
        ]);
        AuditLog::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $fromDate = now()->subDays(3)->format('Y-m-d');
        $toDate = now()->format('Y-m-d');

        $response = $this->get("/audit-log?date_from={$fromDate}&date_to={$toDate}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('auditLogs.data', function ($data) {
            return count($data) === 1;
        })
        );
    }

    public function test_audit_log_model_relationships(): void
    {
        $user = User::factory()->create();
        $auditLog = AuditLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $auditLog->user);
        $this->assertEquals($user->id, $auditLog->user->id);
    }

    public function test_audit_log_model_scopes(): void
    {
        $user = User::factory()->create();

        AuditLog::factory()->create([
            'event' => 'created',
            'user_id' => $user->id,
            'auditable_type' => User::class,
        ]);

        AuditLog::factory()->create([
            'event' => 'updated',
            'user_id' => $user->id,
            'auditable_type' => User::class,
        ]);

        // Test byEvent scope
        $createdLogs = AuditLog::byEvent('created')->get();
        $this->assertCount(1, $createdLogs);
        $this->assertEquals('created', $createdLogs->first()->event);

        // Test byUser scope
        $userLogs = AuditLog::byUser($user->id)->get();
        $this->assertCount(2, $userLogs);

        // Test byModel scope
        $modelLogs = AuditLog::byModel(User::class)->get();
        $this->assertCount(2, $modelLogs);
    }
}
