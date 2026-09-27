<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequestRequest;
use App\Http\Requests\UpdatePurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\PurchaseRequest;
use App\Models\Terrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    /**
     * GET /api/purchase-requests
     * Filtres : ?q= (description/terrain_titre), ?status=, ?terrain_id=, ?client_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PurchaseRequest::class);

        $query = PurchaseRequest::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('description', 'ilike', "%{$terme}%")
                  ->orWhere('terrain_titre', 'ilike', "%{$terme}%")
                  ->orWhere('client_nom', 'ilike', "%{$terme}%")
                  ->orWhere('client_prenom', 'ilike', "%{$terme}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('terrain_id')) {
            $query->where('terrain_id', $request->integer('terrain_id'));
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $query->with($aCharger);
        }

        $sortable = ['created_at', 'updated_at', 'status'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return PurchaseRequestResource::collection($query->paginate($perPage))->response();
    }

    /**
     * POST /api/purchase-requests
     * Copie automatiquement les infos client et terrain au moment de la demande.
     */
    public function store(StorePurchaseRequestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'nouvelle';

        $client = Client::findOrFail($data['client_id']);
        $terrain = Terrain::findOrFail($data['terrain_id']);

        $data['client_nom'] = $client->nom;
        $data['client_prenom'] = $client->prenom;
        $data['client_telephone'] = $client->numero;
        $data['client_email'] = $client->mail;

        $data['terrain_titre'] = $terrain->titre;
        $data['terrain_localisation'] = $terrain->localisation;
        $data['terrain_prix'] = $terrain->prix_terrain;

        $purchaseRequest = DB::transaction(function () use ($data, $client, $terrain) {
            $purchaseRequest = PurchaseRequest::create($data);

            $client->increment('nombre_demande_achat');
            $terrain->increment('nombre_demande_achat');

            return $purchaseRequest;
        });

        $this->tracer('purchase_requests.create', $purchaseRequest);

        return (new PurchaseRequestResource($purchaseRequest))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/purchase-requests/{purchase_request}
     * ?include=client,terrain,visits,reservations
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('view', $purchaseRequest);

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $purchaseRequest->load($aCharger);
        }

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * PUT/PATCH /api/purchase-requests/{purchase_request}
     */
    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        $data = $request->validated();
        $avant = $purchaseRequest->only(array_keys($data));

        $purchaseRequest->update($data);

        $this->tracer('purchase_requests.update', $purchaseRequest, $avant, $purchaseRequest->only(array_keys($data)));

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * DELETE /api/purchase-requests/{purchase_request}
     * Pas de soft delete pour cette table (absent du dictionnaire) : suppression définitive.
     */
    public function destroy(PurchaseRequest $purchaseRequest): JsonResponse
    {
        $this->authorize('delete', $purchaseRequest);

        DB::transaction(function () use ($purchaseRequest) {
            $client = $purchaseRequest->client;
            $terrain = $purchaseRequest->terrain;

            $purchaseRequest->delete();

            if ($client) {
                $client->decrement('nombre_demande_achat');
            }
            if ($terrain) {
                $terrain->decrement('nombre_demande_achat');
            }
        });

        $this->tracer('purchase_requests.delete', $purchaseRequest);

        return response()->json(['message' => "Demande d'achat supprimée."]);
    }

    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = ['client', 'terrain', 'visits', 'reservations'];

        if (! $request->filled('include')) {
            return [];
        }

        $demandees = explode(',', $request->string('include'));

        return array_values(array_intersect($demandees, $relationsAutorisees));
    }

    private function tracer(string $action, PurchaseRequest $purchaseRequest, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'purchase_requests',
            'entity_id' => $purchaseRequest->id,
            'entity_label' => $purchaseRequest->client_nom.' '.$purchaseRequest->client_prenom.' — '.$purchaseRequest->terrain_titre,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
