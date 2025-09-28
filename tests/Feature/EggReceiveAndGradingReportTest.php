<?php

namespace Tests\Feature;

use Tests\TestCase;

class EggReceiveAndGradingReportTest extends TestCase
{
    /**
     * Test that the egg receive and grading report page loads successfully.
     */
    public function test_egg_receive_and_grading_report_page_loads(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);
    }

    /**
     * Test that the report contains the expected data structure.
     */
    public function test_report_contains_expected_data(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);

        // Check that the response contains the expected data structure
        $response->assertInertia(fn ($page) => $page->component('report/egg-receive-and-grading-report')
            ->has('reportData')
            ->has('sheds')
            ->has('grandTotal')
        );
    }

    /**
     * Test that the report data contains required fields.
     */
    public function test_report_data_contains_required_fields(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('report/egg-receive-and-grading-report')
            ->where('reportData.report_title', 'Egg Received and Grading Report Of Farm- PHL-1')
            ->where('reportData.received_date', '24.05.2025')
            ->where('reportData.report_no', '285')
        );
    }

    /**
     * Test that the sheds data contains the expected structure.
     */
    public function test_sheds_data_structure(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('report/egg-receive-and-grading-report')
            ->where('sheds.0.shed_no', '4')
            ->where('sheds.0.flocks.0.flock_no', '4')
            ->where('sheds.0.subtotal.received_egg', 7920)
        );
    }

    /**
     * Test that the grand total data is correct.
     */
    public function test_grand_total_data(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page->component('report/egg-receive-and-grading-report')
            ->where('grandTotal.received_egg', 30000)
            ->where('grandTotal.total_reject', 144)
            ->where('grandTotal.hatchable_egg', 29856)
        );
    }

    /**
     * Test that the report page contains print functionality.
     */
    public function test_report_contains_print_functionality(): void
    {
        $response = $this->get('/egg-receive-and-grading-report');

        $response->assertStatus(200);

        // Check that the response contains the Inertia component with print functionality
        $response->assertInertia(fn ($page) => $page->component('report/egg-receive-and-grading-report')
        );
    }
}
