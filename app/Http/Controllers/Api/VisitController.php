<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Terrain;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    /**
     * GET /api/visits
     * Filtres : ?status=, ?terrain_id=, ?client_id=, ?date_visite=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Visit::class);

        $query = Visit::query();

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('terrain_id')) {
            $query->where('terrain_id', $request->integer('terrain_id'));
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('date_visite')) {
            $query->whereDate('date_visite', $request->string('date_visite'));
        }

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $query->with($aCharger);
        }

        $sortable = ['created_at', 'updated_at', 'date_visite', 'status'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return VisitResource::collection($query->paginate($perPage))->response();
    }

    /**
     * POST /api/visits
     */
    public function store(StoreVisitRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'demandee';

        $client = Client::findOrFail($data['client_id']);
        $terrain = Terrain::findOrFail($data['terrain_id']);

        $data['client_nom'] = $client->nom;
        $data['client_prenom'] = $client->prenom;
        $data['client_telephone'] = $client->numero;
        $data['client_email'] = $client->mail;

        $data['terrain_titre'] = $terrain->titre;
        $data['terrain_localisation'] = $terrain->localisation;
        $data['terrain_prix'] = $terrain->prix_terrain;

        if (! empty($data['responsable_id'])) {
            $responsable = User::find($data['responsable_id']);
            if ($responsable) {
                $data['responsable_nom'] = trim($responsable->nom.' '.$responsable->prenom);
                $data['responsable_telephone'] = $responsable->telephone;
            }
        }

        $visit = DB::transaction(function () use ($data, $client, $terrain) {
            $visit = Visit::create($data);

            $client->increment('nombre_visite_planifiee');
            $terrain->increment('nombre_visite_planifiee');

            return $visit;
        });

        $this->tracer('visits.create', $visit);

        return (new VisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/visits/{visit}
     * ?include=client,terrain,purchaseRequest
     */
    public function show(Request $request, Visit $visit): JsonResponse
    {
        $this->authorize('view', $visit);

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $visit->load($aCharger);
        }

        return (new VisitResource($visit))->response();
    }

    /**
     * PUT/PATCH /api/visits/{visit}
     * Si le statut passe à "effectuee" pour la première fois, incrémente les compteurs
     * nombre_visite_effectuee du client et du terrain.
     */
    public function update(UpdateVisitRequest $request, Visit $visit): JsonResponse
    {
        $data = $request->validated();
        $avant = $visit->only(array_keys($data));
        $statutAvant = $visit->status;

        DB::transaction(function () use ($visit, $data, $statutAvant) {
            $visit->update($data);

            $passeAEffectuee = ($data['status'] ?? null) === 'effectuee' && $statutAvant !== 'effectuee';

            if ($passeAEffectuee) {
                $visit->client?->increment('nombre_visite_effectuee');
                $visit->terrain?->increment('nombre_visite_effectuee');
            }
        });

        $this->tracer('visits.update', $visit, $avant, $visit->only(array_keys($data)));

        return (new VisitResource($visit))->response();
    }

    /**
     * DELETE /api/visits/{visit}
     * Pas de soft delete pour cette table (absent du dictionnaire) : suppression définitive.
     */
    public function destroy(Visit $visit): JsonResponse
    {
        $this->authorize('delete', $visit);

        DB::transaction(function () use ($visit) {
            $client = $visit->client;
            $terrain = $visit->terrain;
            $etaitEffectuee = $visit->status === 'effectuee';

            $visit->delete();

            if ($client) {
                $client->decrement('nombre_visite_planifiee');
                if ($etaitEffectuee) {
                    $client->decrement('nombre_visite_effectuee');
                }
            }
            if ($terrain) {
                $terrain->decrement('nombre_visite_planifiee');
                if ($etaitEffectuee) {
                    $terrain->decrement('nombre_visite_effectuee');
                }
            }
        });

        $this->tracer('visits.delete', $visit);

        return response()->json(['message' => 'Visite supprimée.']);
    }

    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = ['client', 'terrain', 'purchaseRequest'];

        if (! $request->filled('include')) {
            return [];
        }

        $demandees = explode(',', $request->string('include'));

        return array_values(array_intersect($demandees, $relationsAutorisees));
    }

    private function tracer(string $action, Visit $visit, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'visits',
            'entity_id' => $visit->id,
            'entity_label' => $visit->client_nom.' '.$visit->client_prenom.' — '.$visit->terrain_titre,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
