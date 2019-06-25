<?php

namespace ValidationManagerTests\Feature\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use ValidationManagerTests\Feature\Requests\TestRequest;

class TestController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function test(TestRequest $request)
    {
        return 'Validation successful';
    }

}
