<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Client;
use App\Policies\ClientPolicy;
use App\Models\SearchRequest;
use App\Policies\SearchRequestPolicy;
use App\Models\Terrain;
use App\Policies\TerrainPolicy;
use App\Models\PurchaseRequest;
use App\Policies\PurchaseRequestPolicy;
use App\Models\Visit;
use App\Policies\VisitPolicy;
use App\Models\Reservation;
use App\Policies\ReservationPolicy;
use Illuminate\Support\Facades\Gate;


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
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(SearchRequest::class, SearchRequestPolicy::class);
        Gate::policy(Terrain::class, TerrainPolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
    }
}