<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['openJobs' => JobPosting::open()->count()]);
    }

    public function services(): View
    {
        return view('pages.services');
    }

    public function service(string $service): View
    {
        $all = (array) sheriff('services');
        abort_unless(isset($all[$service]), 404);

        return view('pages.service', ['slug' => $service, 'service' => $all[$service], 'others' => array_diff_key($all, [$service => true])]);
    }

    public function industries(): View
    {
        return view('pages.industries');
    }

    public function hse(): View
    {
        return view('pages.hse');
    }

    public function group(): View
    {
        return view('pages.group');
    }

    public function leadership(): View
    {
        return view('pages.leadership');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function credits(): View
    {
        return view('pages.credits');
    }

    public function sitemap(): View
    {
        return view('pages.sitemap', ['jobs' => JobPosting::open()->orderBy('title')->get()]);
    }

    public function sitemapXml(): Response
    {
        $urls = collect(['home', 'services', 'industries', 'hse', 'group', 'leadership', 'careers', 'rfp.create', 'vendor.create', 'alarm-service', 'contact', 'privacy', 'credits', 'sitemap'])
            ->map(fn (string $r) => route($r))
            ->merge(array_map(fn (string $s) => route('services.show', $s), array_keys((array) sheriff('services'))))
            ->merge(JobPosting::open()->get()->map(fn (JobPosting $j) => route('careers.show', $j)));

        return response()->view('sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml']);
    }
}
