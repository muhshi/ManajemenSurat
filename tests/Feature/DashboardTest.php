<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class DashboardTest extends TestCase
{
    public function test_dashboard_loads()
    {
        Log::info('Test: Starting dashboard load test');

        // Use first user from real DB
        $user = User::first();

        if (!$user) {
            Log::error('Test: No users found in DB');
            $this->markTestSkipped('No users found in database.');
        }

        Log::info('Test: User found (ID: ' . $user->id . '), authenticating');

        $this->actingAs($user);

        Log::info('Test: Requesting /admin');

        $response = $this->get('/admin');

        Log::info('Test: Request finished with status ' . $response->status());

        $response->assertStatus(200);
    }
}
