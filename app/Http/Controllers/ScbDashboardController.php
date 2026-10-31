<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\scb\ScbDashboardService;

class ScbDashboardController extends Controller
{
    public function __construct(
        protected ScbDashboardService $ScbDashboardService
    ) {
    }

    public function index()
    {
        $data = $this->ScbDashboardService->getDashboardData();

        return view('scb.index', $data);
    }
}
