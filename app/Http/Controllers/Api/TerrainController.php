<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcheterTerrainRequest;
use App\Http\Requests\StoreTerrainRequest;
use App\Http\Requests\UpdateTerrainRequest;
use App\Http\Resources\TerrainResource;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Terrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerrainController extends Controller
{
    /**
     * GET /api/terrains
     * Filtres : ?q= (titre/zone/reference), ?status=, ?zone=, ?proprietaire_id=, ?verified=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Terrain::class);

        $query = Terrain::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('titre', 'ilike', "%{$terme}%")
                  ->orWhere('reference', 'ilike', "%{$terme}%")
                  ->orWhere('zone', 'ilike', "%{$terme}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('zone')) {
            $query->where('zone', 'ilike', '%'.$request->string('zone').'%');
        }

        if ($request->has('proprietaire_id')) {
            $query->where('proprietaire_id', $request->integer('proprietaire_id'));
        }

        if ($request->has('verified')) {
            $query->where('verified', $request->boolean('verified'));
        }

        if ($request->query('trashed') === 'only') {
            $query->onlyTrashed();
        } elseif ($request->boolean('trashed')) {
            $query->withTrashed();
        }

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $query->with($aCharger);
        }

        $sortable = ['created_at', 'updated_at', 'prix_terrain', 'superficie', 'status'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return TerrainResource::collection($query->paginate($perPage))->response();
    }

    /**
     * POST /api/terrains
     * proprietaire_nom/prenom/telephone/... sont copiés automatiquement depuis le client.
     */
    public function store(StoreTerrainRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'brouillon';

        $proprietaire = Client::findOrFail($data['proprietaire_id']);
        $data['proprietaire_nom'] = $proprietaire->nom;
        $data['proprietaire_prenom'] = $proprietaire->prenom;
        $data['proprietaire_telephone'] = $proprietaire->numero;
        $data['proprietaire_email'] = $proprietaire->mail;
        $data['proprietaire_pays_residence'] = $proprietaire->pays_residence;
        $data['proprietaire_nationalite'] = $proprietaire->nationalite;
        $data['proprietaire_profession'] = $proprietaire->profession;
        $data['proprietaire_piece_identite'] = $proprietaire->piece_identite;

        $terrain = DB::transaction(function () use ($data, $proprietaire) {
            $terrain = Terrain::create($data);
            $proprietaire->increment('nombre_terrain_propose');

            return $terrain;
        });

        $this->tracer('terrains.create', $terrain);

        return (new TerrainResource($terrain))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/terrains/{terrain}
     * ?include=proprietaire,acheteur,visits,reservations,purchaseRequests,transactions,paymentSchedules
     */
    public function show(Request $request, Terrain $terrain): JsonResponse
    {
        $this->authorize('view', $terrain);

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $terrain->load($aCharger);
        }

        return (new TerrainResource($terrain))->response();
    }

    /**
     * PUT/PATCH /api/terrains/{terrain}
     */
    public function update(UpdateTerrainRequest $request, Terrain $terrain): JsonResponse
    {
        $data = $request->validated();
        $avant = $terrain->only(array_keys($data));

        $terrain->update($data);

        $this->tracer('terrains.update', $terrain, $avant, $terrain->only(array_keys($data)));

        return (new TerrainResource($terrain))->response();
    }

    /**
     * POST /api/terrains/{terrain}/acheter
     * Achat direct : vend le terrain sans passer par purchase_requests/reservations.
     */
    public function acheter(AcheterTerrainRequest $request, Terrain $terrain): JsonResponse
    {
        $this->authorize('update', $terrain);

        if ($terrain->status === 'vendu') {
            return response()->json(['message' => 'Ce terrain est déjà vendu.'], 409);
        }

        $data = $request->validated();

        if ((int) $data['client_id'] === (int) $terrain->proprietaire_id) {
            return response()->json(['message' => 'Le propriétaire ne peut pas acheter son propre terrain.'], 422);
        }

        $acheteur = Client::findOrFail($data['client_id']);

        $terrain = DB::transaction(function () use ($terrain, $acheteur, $data) {
            $terrain->update([
                'acheteur_id' => $acheteur->id,
                'acheteur_nom' => $acheteur->nom,
                'acheteur_prenom' => $acheteur->prenom,
                'acheteur_telephone' => $acheteur->numero,
                'acheteur_email' => $acheteur->mail,
                'acheteur_pays_residence' => $acheteur->pays_residence,
                'acheteur_nationalite' => $acheteur->nationalite,
                'acheteur_profession' => $acheteur->profession,
                'acheteur_piece_identite' => $acheteur->piece_identite,
                'prix_achat_client' => $data['prix_final'] ?? $terrain->prix_terrain,
                'status' => 'vendu',
            ]);

            $terrain->increment('nombre_transaction');

            if ($terrain->proprietaire) {
                $terrain->proprietaire->increment('nombre_terrain_vendu');
            }

            return $terrain;
        });

        $this->tracer('terrains.acheter', $terrain, null, ['acheteur_id' => $acheteur->id, 'status' => 'vendu']);

        return (new TerrainResource($terrain))->response();
    }

    /**
     * DELETE /api/terrains/{terrain}
     * Suppression logique : l'historique (visites, transactions) reste intact.
     */
    public function destroy(Terrain $terrain): JsonResponse
    {
        $this->authorize('delete', $terrain);

        $terrain->delete();

        $this->tracer('terrains.delete', $terrain);

        return response()->json(['message' => 'Terrain supprimé (soft delete).']);
    }

    /**
     * POST /api/terrains/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        $terrain = Terrain::withTrashed()->findOrFail($id);
        $this->authorize('restore', $terrain);

        $terrain->restore();

        $this->tracer('terrains.restore', $terrain);

        return (new TerrainResource($terrain))->response();
    }

    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = [
            'proprietaire', 'acheteur', 'purchaseRequests',
            'visits', 'reservations', 'transactions', 'paymentSchedules',
        ];

        if (! $request->filled('include')) {
            return [];
        }

        $demandees = explode(',', $request->string('include'));

        return array_values(array_intersect($demandees, $relationsAutorisees));
    }

    private function tracer(string $action, Terrain $terrain, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'terrains',
            'entity_id' => $terrain->id,
            'entity_label' => $terrain->reference.' — '.$terrain->titre,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
