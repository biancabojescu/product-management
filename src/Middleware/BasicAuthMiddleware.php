<?php

namespace App\Middleware;

use App\Http\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMiddleware {
    public static function handle(): ?Response {
        $username = $_ENV['DASHBOARD_USERNAME'] ?? '';
        $password = $_ENV['DASHBOARD_PASSWORD'] ?? '';

        if ($username === '' || $password === '') {
           error_log('Dashboard credentials are not configured');
           return ApiResponse::error('auth.config_error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $providedUsername = $_SERVER['PHP_AUTH_USER'] ?? '';
        $providedPassword = $_SERVER['PHP_AUTH_PW'] ?? '';

        if (!hash_equals($username, $providedUsername) || !hash_equals($password, $providedPassword)) {
            $response = ApiResponse::error('auth.unauthorized', Response::HTTP_UNAUTHORIZED);
            $response->headers->set('WWW-Authenticate', 'Basic realm="Dashboard"');

            return $response;
        }

        return null;
    }
}