<?php

namespace App\Providers;

use App\Events\AppointmentConfirmed;
use App\Events\PrescriptionRequestConfirmed;
use App\Events\TestPaymentRequested;
use App\Events\TestRequestConfirmed;
use App\Events\TestResultReady;
use App\Listeners\ConfirmedPrescriptionRequest;
use App\Listeners\SendAppointmentConfirmedNotification;
use App\Listeners\SendTestPaymentNotification;
use App\Listeners\SendTestResultNotification;
use App\Listeners\SendTestToConductNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            AppointmentConfirmed::class,
            SendAppointmentConfirmedNotification::class

        );
        Event::listen(
            TestPaymentRequested::class,
            SendTestPaymentNotification::class
        );
        Event::listen(
            TestRequestConfirmed::class,
            SendTestToConductNotification::class
        );
        Event::listen(
            TestResultReady::class,
            SendTestResultNotification::class
        );

        Event::listen(
            PrescriptionRequestConfirmed::class,
            ConfirmedPrescriptionRequest::class,
        );
    }
}
