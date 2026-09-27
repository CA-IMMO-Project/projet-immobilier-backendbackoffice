<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSearchRequestRequest;
use App\Http\Requests\UpdateSearchRequestRequest;
use App\Http\Resources\SearchRequestResource;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\SearchRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchRequestController extends Controller
{
    /**
     * GET /api/search-requests
     * Filtres : ?q= (description/zone), ?status=, ?client_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SearchRequest::class);

        $query = SearchRequest::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('description', 'ilike', "%{$terme}%")
                  ->orWhere('zone_recherche', 'ilike', "%{$terme}%")
                  ->orWhere('client_nom', 'ilike', "%{$terme}%")
                  ->orWhere('client_prenom', 'ilike', "%{$terme}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($this->relationsDemandees($request)) {
            $query->with($this->relationsDemandees($request));
        }

        $sortable = ['created_at', 'updated_at', 'status', 'budget_max'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return SearchRequestResource::collection($query->paginate($perPage))->response();
    }

    /**
     * POST /api/search-requests
     * Les champs client_nom/prenom/telephone/email sont copiés automatiquement depuis le client,
     * jamais saisis à la main (voir StoreSearchRequestRequest).
     */
    public function store(StoreSearchRequestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'nouvelle';

        $client = Client::findOrFail($data['client_id']);
        $data['client_nom'] = $client->nom;
        $data['client_prenom'] = $client->prenom;
        $data['client_telephone'] = $client->numero;
        $data['client_email'] = $client->mail;

        $searchRequest = DB::transaction(function () use ($data, $client) {
            $searchRequest = SearchRequest::create($data);

            // Compteur mis à jour dans la même transaction que l'opération qui le déclenche (règle 3 du dictionnaire)
            $client->increment('nombre_demande_recherche');

            return $searchRequest;
        });

        $this->tracer('search_requests.create', $searchRequest);

        return (new SearchRequestResource($searchRequest))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/search-requests/{search_request}
     * ?include=client pour charger la fiche complète du client actuel.
     */
    public function show(Request $request, SearchRequest $searchRequest): JsonResponse
    {
        $this->authorize('view', $searchRequest);

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $searchRequest->load($aCharger);
        }

        return (new SearchRequestResource($searchRequest))->response();
    }

    /**
     * PUT/PATCH /api/search-requests/{search_request}
     */
    public function update(UpdateSearchRequestRequest $request, SearchRequest $searchRequest): JsonResponse
    {
        $data = $request->validated();
        $avant = $searchRequest->only(array_keys($data));

        $searchRequest->update($data);

        $this->tracer('search_requests.update', $searchRequest, $avant, $searchRequest->only(array_keys($data)));

        return (new SearchRequestResource($searchRequest))->response();
    }

    /**
     * DELETE /api/search-requests/{search_request}
     * Pas de soft delete pour cette table (absent du dictionnaire) : suppression définitive.
     */
    public function destroy(SearchRequest $searchRequest): JsonResponse
    {
        $this->authorize('delete', $searchRequest);

        DB::transaction(function () use ($searchRequest) {
            $client = $searchRequest->client;
            $searchRequest->delete();

            if ($client) {
                $client->decrement('nombre_demande_recherche');
            }
        });

        $this->tracer('search_requests.delete', $searchRequest);

        return response()->json(['message' => 'Demande de recherche supprimée.']);
    }

    /**
     * Valide les relations demandées via ?include=client
     */
    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = ['client'];

        if (! $request->filled('include')) {
            return [];
        }

        $demandees = explode(',', $request->string('include'));

        return array_values(array_intersect($demandees, $relationsAutorisees));
    }

    /**
     * Petite aide pour tracer l'action dans audit_logs (append-only).
     */
    private function tracer(string $action, SearchRequest $searchRequest, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'search_requests',
            'entity_id' => $searchRequest->id,
            'entity_label' => $searchRequest->client_nom.' '.$searchRequest->client_prenom.' — '.$searchRequest->zone_recherche,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
