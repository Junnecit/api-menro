<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\GoogleOAuthCodeService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GoogleOAuthCodeServiceTest extends TestCase
{
    public function test_issue_and_consume_code(): void
    {
        $service = new GoogleOAuthCodeService();

        $user = new User();
        $user->id = 9999;

        $code = $service->issue($user);
        $this->assertNotEmpty($code);
        $this->assertSame(64, strlen($code));

        // When stored, it should be in cache
        $cachedId = Cache::store('file')->get('google_oauth_exchange:'.$code);
        $this->assertSame(9999, $cachedId);

        // Consume pulls the cached user id
        $pulledId = Cache::store('file')->pull('google_oauth_exchange:'.$code);
        $this->assertSame(9999, $pulledId);

        // A second pull or invalid code returns null
        $this->assertNull(Cache::store('file')->pull('google_oauth_exchange:'.$code));
        $this->assertNull($service->consume('invalid_code_12345678901234567890123456789012'));
    }
}
