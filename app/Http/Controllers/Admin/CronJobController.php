<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CronJobController extends Controller
{
    public function index()
    {
        $projectPath = base_path();
        return view('admin.settings.cron', compact('projectPath'));
    }
}
