<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AlarmServiceController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorPackController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/services/{service}', [PageController::class, 'service'])->name('services.show');
Route::get('/industries', [PageController::class, 'industries'])->name('industries');
Route::get('/hse-and-compliance', [PageController::class, 'hse'])->name('hse');
Route::get('/group-companies', [PageController::class, 'group'])->name('group');
Route::get('/leadership', [PageController::class, 'leadership'])->name('leadership');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:forms')->name('contact.store');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/photo-credits', [PageController::class, 'credits'])->name('credits');
Route::get('/sitemap', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap.xml', [PageController::class, 'sitemapXml'])->name('sitemap.xml');

// Careers
Route::get('/careers', [CareerController::class, 'index'])->name('careers');
Route::get('/careers/apply', [ApplicationController::class, 'create'])->name('apply.create');
Route::post('/careers/apply', [ApplicationController::class, 'store'])->middleware('throttle:forms')->name('apply.store');
Route::get('/careers/apply/thanks', [ApplicationController::class, 'thanks'])->name('apply.thanks');
Route::get('/careers/{job}', [CareerController::class, 'show'])->name('careers.show');

// Request for proposal
Route::get('/request-a-proposal', [ProposalController::class, 'create'])->name('rfp.create');
Route::post('/request-a-proposal', [ProposalController::class, 'store'])->middleware('throttle:forms')->name('rfp.store');
Route::get('/request-a-proposal/thanks', [ProposalController::class, 'thanks'])->name('rfp.thanks');

// Vendor prequalification pack: request → staff approval → time-limited signed link
Route::get('/vendor-prequalification', [VendorController::class, 'create'])->name('vendor.create');
Route::post('/vendor-prequalification', [VendorController::class, 'store'])->middleware('throttle:forms')->name('vendor.store');
Route::get('/vendor-prequalification/thanks', [VendorController::class, 'thanks'])->name('vendor.thanks');
Route::middleware(['signed', 'throttle:downloads'])->group(function () {
    Route::get('/vendor-pack/{token}', [VendorPackController::class, 'show'])->name('vendor-pack.show');
    Route::get('/vendor-pack/{token}/{document}', [VendorPackController::class, 'download'])->whereNumber('document')->name('vendor-pack.download');
});

// Sheriff Alarms customers
Route::get('/alarm-service', [AlarmServiceController::class, 'create'])->name('alarm-service');
Route::post('/alarm-service', [AlarmServiceController::class, 'store'])->middleware('throttle:forms')->name('alarm-service.store');
Route::get('/alarm-service/thanks', [AlarmServiceController::class, 'thanks'])->name('alarm-service.thanks');
Route::post('/alarm-service/status', [AlarmServiceController::class, 'lookup'])->middleware('throttle:lookups')->name('alarm-service.lookup');
Route::get('/alarm-service/status', fn () => to_route('alarm-service', ['tab' => 'status']));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::middleware('area:sales')->group(function () {
        Route::get('/proposals', [Admin\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/board', [Admin\ProposalController::class, 'board'])->name('proposals.board');
        Route::get('/proposals/{proposal}', [Admin\ProposalController::class, 'show'])->name('proposals.show');
        Route::patch('/proposals/{proposal}', [Admin\ProposalController::class, 'update'])->name('proposals.update');
        Route::get('/proposals/{proposal}/documents/{document}', [Admin\ProposalController::class, 'document'])->name('proposals.document');

        Route::get('/vendor-requests', [Admin\VendorRequestController::class, 'index'])->name('vendor.index');
        Route::get('/vendor-requests/{vendorRequest}', [Admin\VendorRequestController::class, 'show'])->name('vendor.show');
        Route::post('/vendor-requests/{vendorRequest}/approve', [Admin\VendorRequestController::class, 'approve'])->name('vendor.approve');
        Route::post('/vendor-requests/{vendorRequest}/decline', [Admin\VendorRequestController::class, 'decline'])->name('vendor.decline');
        Route::post('/vendor-requests/{vendorRequest}/revoke', [Admin\VendorRequestController::class, 'revoke'])->name('vendor.revoke');

        Route::resource('compliance', Admin\ComplianceDocumentController::class)->except('show')->parameters(['compliance' => 'document']);
        Route::get('/compliance/{document}/file', [Admin\ComplianceDocumentController::class, 'file'])->name('compliance.file');

        Route::get('/alarm-tickets', [Admin\TicketController::class, 'index'])->name('tickets.index');
        Route::get('/alarm-tickets/{ticket}', [Admin\TicketController::class, 'show'])->name('tickets.show');
        Route::patch('/alarm-tickets/{ticket}', [Admin\TicketController::class, 'update'])->name('tickets.update');

        Route::get('/messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::patch('/messages/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
    });

    Route::middleware('area:hiring')->group(function () {
        Route::resource('jobs', Admin\JobPostingController::class)->except('show')->parameters(['jobs' => 'job']);
        Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/pipeline', [Admin\ApplicationController::class, 'pipeline'])->name('applications.pipeline');
        Route::get('/applications/export', [Admin\ApplicationController::class, 'export'])->name('applications.export');
        Route::get('/applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{application}', [Admin\ApplicationController::class, 'update'])->name('applications.update');
        Route::get('/applications/{application}/documents/{document}', [Admin\ApplicationController::class, 'document'])->name('applications.document');
    });

    Route::middleware('area:admin')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    });
});
