<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function show(Request $request): DashboardResource
    {
        return DashboardResource::make(
            $this->dashboardService->summary($request->user()),
        );
    }
}
