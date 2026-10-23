<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        return view('pages.careers.index', ['jobs' => JobPosting::open()->orderBy('department')->orderBy('title')->get()]);
    }

    public function show(JobPosting $job): View
    {
        abort_unless(JobPosting::open()->whereKey($job->id)->exists(), 404);

        return view('pages.careers.show', ['job' => $job]);
    }
}
