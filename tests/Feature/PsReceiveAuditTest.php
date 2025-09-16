<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Supplier;
use App\Models\Country;
use App\Models\Ps\PsReceive;
use App\Models\Ps\PsChickCount;
use App\Models\Ps\PsLabTest;
use App\Models\Ps\PsReceiveAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PsReceiveAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_ps_receive_store_creates_audit_logs(): void
    {
        // Create test data
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $company = Company::factory()->create();
        $country = Country::factory()->create();
        $breedType = BreedType::factory()->create();

        $this->actingAs($user);
        
        // Disable CSRF protection for testing
        $this->withoutMiddleware();

        // Create transport type data
        DB::table('transport_types')->insert([
            ['id' => 1, 'name' => 'Truck', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $requestData = [
            'shipment_type_id' => 1,
            'pi_no' => 'PI-001',
            'pi_date' => '2024-01-01',
            'order_no' => 'ORD-001',
            'order_date' => '2024-01-02',
            'lc_no' => 'LC-001',
            'lc_date' => '2024-01-03',
            'supplier_id' => $supplier->id,
            'breed_type' => [$breedType->id],
            'country_of_origin' => $country->id,
            'transport_type' => 1,
            'company_id' => $company->id,
            'vehicle_inside_temp' => 25.5,
            'remarks' => 'Test remarks',
            'ps_male_rec_box' => 10,
            'ps_male_qty' => 100,
            'ps_female_rec_box' => 10,
            'ps_female_qty' => 100,
            'ps_total_qty' => 200,
            'ps_total_re_box_qty' => 20,
            'ps_challan_box_qty' => 20,
            'ps_gross_weight' => 50.5,
            'ps_net_weight' => 48.5,
            'gov_lab_send_female_qty' => 5,
            'gov_lab_send_male_qty' => 5,
            'gov_lab_send_total_qty' => 10,
            'provita_lab_send_female_qty' => 5,
            'provita_lab_send_male_qty' => 5,
            'provita_lab_send_total_qty' => 10,
        ];

        // Make the request
        $response = $this->post(route('ps-receive.store'), $requestData);

        // Assert the response is successful
        $response->assertRedirect(route('ps-receive.index'));

        // Assert PsReceive was created
        $this->assertDatabaseHas('ps_receives', [
            'pi_no' => 'PI-001',
            'created_by' => $user->id,
        ]);

        $psReceive = PsReceive::where('pi_no', 'PI-001')->first();

        // Assert audit logs were created for all models
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => PsReceive::class,
            'auditable_id' => $psReceive->id,
            'user_id' => $user->id,
        ]);

        $chickCount = PsChickCount::where('ps_receive_id', $psReceive->id)->first();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => PsChickCount::class,
            'auditable_id' => $chickCount->id,
            'user_id' => $user->id,
        ]);

        $govLabTransfer = PsLabTest::where('ps_receive_id', $psReceive->id)
            ->where('lab_type', 1)
            ->first();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => PsLabTest::class,
            'auditable_id' => $govLabTransfer->id,
            'user_id' => $user->id,
        ]);

        $provitaLabTransfer = PsLabTest::where('ps_receive_id', $psReceive->id)
            ->where('lab_type', 2)
            ->first();
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => PsLabTest::class,
            'auditable_id' => $provitaLabTransfer->id,
            'user_id' => $user->id,
        ]);

        // Verify total audit logs created
        $auditLogsCount = AuditLog::where('user_id', $user->id)
            ->where('event', 'created')
            ->count();
        
        $this->assertEquals(4, $auditLogsCount, 'Expected 4 audit logs (PsReceive, PsChickCount, 2 PsLabTest)');
    }

    public function test_ps_receive_store_with_file_attachment_creates_audit_log(): void
    {
        // Create test data
        $user = User::factory()->create();
        $supplier = Supplier::factory()->create();
        $company = Company::factory()->create();
        $country = Country::factory()->create();
        $breedType = BreedType::factory()->create();

        $this->actingAs($user);
        
        // Disable CSRF protection for testing
        $this->withoutMiddleware();

        // Create transport type data
        DB::table('transport_types')->insert([
            ['id' => 1, 'name' => 'Truck', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf', 100);

        $requestData = [
            'shipment_type_id' => 1,
            'pi_no' => 'PI-002',
            'pi_date' => '2024-01-01',
            'supplier_id' => $supplier->id,
            'breed_type' => [$breedType->id],
            'country_of_origin' => $country->id,
            'transport_type' => 1,
            'company_id' => $company->id,
            'ps_male_rec_box' => 10,
            'ps_male_qty' => 100,
            'ps_female_rec_box' => 10,
            'ps_female_qty' => 100,
            'ps_total_qty' => 200,
            'ps_total_re_box_qty' => 20,
            'ps_challan_box_qty' => 20,
            'ps_gross_weight' => 50.5,
            'ps_net_weight' => 48.5,
            'gov_lab_send_female_qty' => 5,
            'gov_lab_send_male_qty' => 5,
            'gov_lab_send_total_qty' => 10,
            'provita_lab_send_female_qty' => 5,
            'provita_lab_send_male_qty' => 5,
            'provita_lab_send_total_qty' => 10,
            'file' => [$file],
        ];

        // Make the request
        $response = $this->post(route('ps-receive.store'), $requestData);

        // Assert the response is successful
        $response->assertRedirect(route('ps-receive.index'));

        $psReceive = PsReceive::where('pi_no', 'PI-002')->first();
        $attachment = PsReceiveAttachment::where('ps_receive_id', $psReceive->id)->first();

        // Assert audit log was created for file attachment
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'created',
            'auditable_type' => PsReceiveAttachment::class,
            'auditable_id' => $attachment->id,
            'user_id' => $user->id,
        ]);
    }
}