<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

use App\Http\ApiKernel;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$response = (new ApiKernel())->handle($request);

$response->send();
