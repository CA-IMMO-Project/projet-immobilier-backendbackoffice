<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * GET /api/clients
     * Liste paginée, recherche (?q=) et filtres (?status=, ?acheteur=, ?proprietaire=).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'ilike', "%{$terme}%")
                ->orWhere('prenom', 'ilike', "%{$terme}%")
                ->orWhere('mail', 'ilike', "%{$terme}%")
                ->orWhere('numero', 'ilike', "%{$terme}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->has('acheteur')) {
            $query->where('acheteur', $request->boolean('acheteur'));
        }

        if ($request->has('proprietaire')) {
            $query->where('proprietaire', $request->boolean('proprietaire'));
        }

        //Relations chargeable à la demande: ?include=reservations,visites,terrains
        $aCharger = $this->relationsDemandees($request);
        if($aCharger){
            $query->with($aCharger);
        }

        //Clients supprimés: ?trashed=only ou ?trashed=1 (actifs+supprimés)
        if($request->query('trashed') === 'only'){$query->onlyTrashed();}
        elseif($request->boolean('trashed')){$query->withTrashed();}

        // Tri générique : ?sort=nom ou ?sort=-created_at (le "-" inverse l'ordre).
        // Liste blanche obligatoire : on n'accepte jamais un nom de colonne brut venant du frontend.
        $sortable = ['nom', 'prenom', 'status', 'created_at', 'updated_at'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at','desc');
        }

        // per_page plafonné à 100 pour éviter qu'une requête frontend mal formée
        // ne fasse remonter des milliers de lignes d'un coup.
        $perPage = min($request->integer('per_page', 15), 100);

        $clients = $query->paginate($perPage);

        return ClientResource::collection($clients)->response();
    }

    /**
     * POST /api/clients
     * Création manuelle d'un client par le Back Office (ex: propriétaire démarché hors ligne).
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'actif';

        $client = Client::create($data);

        $this->tracer('clients.create', $client);

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/clients/{client}
     */
    public function show(Request $request,Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        $aCharger = $this->relationsDemandees($request);
        if($aCharger){$client->load($aCharger);}

        return (new ClientResource($client))->response();
    }

    /**
     * PUT/PATCH /api/clients/{client}
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $data = $request->validated();
        $avant = $client->only(array_keys($data));

        $client->update($data);

        $this->tracer('clients.update', $client, $avant, $client->only(array_keys($data)));

        return (new ClientResource($client))->response();
    }

    /**
     * DELETE /api/clients/{client}
     * Suppression logique (soft delete) : l'historique (terrains, visites, transactions) reste intact.
     */
    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);

        $client->update(['status' => 'inactif']);
        $client->delete();

        $this->tracer('clients.delete', $client);

        return response()->json(['message' => 'Client désactivé et supprimé (soft delete).']);
    }

    /**
     * POST /api/clients/{id}/restore
     */
    public function restore(int $id): JsonResponse
    {
        $client = Client::withTrashed()->findOrFail($id);
        $this->authorize('restore', $client);

        $client->restore();
        $client->update(['status' => 'actif']);

        $this->tracer('clients.restore', $client);

        return (new ClientResource($client))->response();
    }

    /**
     * Petite aide pour tracer l'action dans audit_logs (append-only).
     */
    private function tracer(string $action, Client $client, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(), // administrateur ayant réalisé l'action
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'clients',
            'entity_id' => $client->id,
            'entity_label' => $client->nom.' '.$client->prenom,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = ['terrains','searchRequests','purchaseRequests',
                                'visits','reservations'];
        if(!$request->filled('include')){return [];}
        $demandees = explode(',',$request->string('include'));

        return array_values(array_intersect($demandees,$relationsAutorisees));
    }
}