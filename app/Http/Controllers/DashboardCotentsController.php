<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardCotentsController extends Controller
{

public function getMonthlyAssessmentData()
{
     

    return view('AdminDashboard', compact('monthLabels', 'assessmentCounts'));
}


}
