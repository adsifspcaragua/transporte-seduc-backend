<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class SessionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const FRONTEND_ORIGIN = 'http://localhost:5173';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'database',
            'session.connection' => config('database.default'),
            'session.table' => 'sessions',
        ]);
    }

    public function test_csrf_cookie_endpoint_issues_the_xsrf_cookie_for_stateful_requests(): void
    {
        $response = $this
            ->withHeaders($this->statefulHeaders())
            ->get('/sanctum/csrf-cookie');

        $response->assertNoContent();
        $response->assertCookie('XSRF-TOKEN');
        $response->assertCookie(config('session.cookie'));
    }

    public function test_user_can_log_in_and_access_me_with_a_cookie_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $csrfResponse = $this
            ->withHeaders($this->statefulHeaders())
            ->get('/sanctum/csrf-cookie');

        $csrfResponse->assertNoContent();

        $cookies = $this->extractCookies($csrfResponse);

        $loginResponse = $this
            ->withCredentials()
            ->withHeaders($this->statefulHeaders($cookies['XSRF-TOKEN']))
            ->withUnencryptedCookies([
                'XSRF-TOKEN' => $cookies['XSRF-TOKEN'],
                config('session.cookie') => $cookies[config('session.cookie')],
            ])
            ->postJson('/api/login', [
                'login' => $user->email,
                'password' => 'password',
            ]);

        $loginResponse
            ->assertOk()
            ->assertJsonMissingPath('token')
            ->assertJsonPath('user.id', $user->id);

        $sessionCookie = $this->cookieValue(
            $loginResponse,
            config('session.cookie'),
            $cookies[config('session.cookie')]
        );

        $this->app['auth']->forgetGuards();

        $this
            ->withCredentials()
            ->withHeaders($this->statefulHeaders())
            ->withUnencryptedCookies([
                config('session.cookie') => $sessionCookie,
            ])
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_logout_invalidates_the_cookie_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $csrfResponse = $this
            ->withHeaders($this->statefulHeaders())
            ->get('/sanctum/csrf-cookie');

        $cookies = $this->extractCookies($csrfResponse);

        $loginResponse = $this
            ->withCredentials()
            ->withHeaders($this->statefulHeaders($cookies['XSRF-TOKEN']))
            ->withUnencryptedCookies([
                'XSRF-TOKEN' => $cookies['XSRF-TOKEN'],
                config('session.cookie') => $cookies[config('session.cookie')],
            ])
            ->postJson('/api/login', [
                'login' => $user->email,
                'password' => 'password',
            ]);

        $sessionCookie = $this->cookieValue(
            $loginResponse,
            config('session.cookie'),
            $cookies[config('session.cookie')]
        );

        $logoutResponse = $this
            ->withCredentials()
            ->withHeaders($this->statefulHeaders($cookies['XSRF-TOKEN']))
            ->withUnencryptedCookies([
                'XSRF-TOKEN' => $cookies['XSRF-TOKEN'],
                config('session.cookie') => $sessionCookie,
            ])
            ->postJson('/api/logout');

        $logoutResponse->assertOk();

        $loggedOutSessionCookie = $this->cookieValue(
            $logoutResponse,
            config('session.cookie'),
            $sessionCookie
        );

        $this->app['auth']->forgetGuards();

        $this
            ->withCredentials()
            ->withHeaders($this->statefulHeaders())
            ->withUnencryptedCookies([
                config('session.cookie') => $loggedOutSessionCookie,
            ])
            ->getJson('/api/me')
            ->assertUnauthorized();
    }

    /**
     * @return array<string, string>
     */
    private function extractCookies(TestResponse $response): array
    {
        return collect($response->headers->getCookies())
            ->mapWithKeys(fn (Cookie $cookie) => [
                $cookie->getName() => urldecode($cookie->getValue()),
            ])
            ->all();
    }

    private function cookieValue(TestResponse $response, string $name, string $default): string
    {
        $cookie = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie) => $cookie->getName() === $name);

        return $cookie instanceof Cookie ? urldecode($cookie->getValue()) : $default;
    }

    /**
     * @return array<string, string>
     */
    private function statefulHeaders(?string $xsrfToken = null): array
    {
        $headers = [
            'Origin' => self::FRONTEND_ORIGIN,
            'Referer' => self::FRONTEND_ORIGIN.'/login',
            'Accept' => 'application/json',
        ];

        if ($xsrfToken !== null) {
            $headers['X-XSRF-TOKEN'] = $xsrfToken;
        }

        return $headers;
    }
}
