<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Terrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * GET /api/reservations
     * Filtres : ?status=, ?terrain_id=, ?client_id=
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Reservation::class);

        $query = Reservation::query();

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

        $sortable = ['created_at', 'updated_at', 'date_reservation', 'status'];
        $sort = $request->string('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        if (in_array($column, $sortable, true)) {
            $query->orderBy($column, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->integer('per_page', 15), 100);

        return ReservationResource::collection($query->paginate($perPage))->response();
    }

    /**
     * POST /api/reservations
     * Refuse la création si le terrain est déjà vendu ou déjà réservé activement.
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'active';

        $client = Client::findOrFail($data['client_id']);
        $terrain = Terrain::findOrFail($data['terrain_id']);

        if ($terrain->status === 'vendu') {
            return response()->json(['message' => 'Ce terrain est déjà vendu, il ne peut pas être réservé.'], 409);
        }

        $dejaReserve = Reservation::where('terrain_id', $terrain->id)
            ->where('status', 'active')
            ->exists();

        if ($dejaReserve) {
            return response()->json(['message' => 'Ce terrain a déjà une réservation active.'], 409);
        }

        $data['client_nom'] = $client->nom;
        $data['client_prenom'] = $client->prenom;
        $data['client_telephone'] = $client->numero;
        $data['client_email'] = $client->mail;

        $data['terrain_titre'] = $terrain->titre;
        $data['terrain_localisation'] = $terrain->localisation;
        $data['terrain_prix'] = $terrain->prix_terrain;

        $reservation = DB::transaction(function () use ($data, $client, $terrain) {
            $reservation = Reservation::create($data);

            $client->increment('nombre_reservation');
            $terrain->increment('nombre_reservation');

            if ($terrain->status !== 'reserve') {
                $terrain->update(['status' => 'reserve']);
            }

            return $reservation;
        });

        $this->tracer('reservations.create', $reservation);

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/reservations/{reservation}
     * ?include=client,terrain,purchaseRequest,transaction
     */
    public function show(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('view', $reservation);

        $aCharger = $this->relationsDemandees($request);
        if ($aCharger) {
            $reservation->load($aCharger);
        }

        return (new ReservationResource($reservation))->response();
    }

    /**
     * PUT/PATCH /api/reservations/{reservation}
     * NOTE : ne remet pas automatiquement le terrain à "publie" si la réservation est
     * annulée/expirée — décision métier à part entière, à traiter dans une prochaine étape.
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        $data = $request->validated();
        $avant = $reservation->only(array_keys($data));

        $reservation->update($data);

        $this->tracer('reservations.update', $reservation, $avant, $reservation->only(array_keys($data)));

        return (new ReservationResource($reservation))->response();
    }

    /**
     * DELETE /api/reservations/{reservation}
     * Pas de soft delete pour cette table (absent du dictionnaire) : suppression définitive.
     */
    public function destroy(Reservation $reservation): JsonResponse
    {
        $this->authorize('delete', $reservation);

        DB::transaction(function () use ($reservation) {
            $client = $reservation->client;
            $terrain = $reservation->terrain;

            $reservation->delete();

            if ($client) {
                $client->decrement('nombre_reservation');
            }
            if ($terrain) {
                $terrain->decrement('nombre_reservation');
            }
        });

        $this->tracer('reservations.delete', $reservation);

        return response()->json(['message' => 'Réservation supprimée.']);
    }

    private function relationsDemandees(Request $request): array
    {
        $relationsAutorisees = ['client', 'terrain', 'purchaseRequest', 'transaction'];

        if (! $request->filled('include')) {
            return [];
        }

        $demandees = explode(',', $request->string('include'));

        return array_values(array_intersect($demandees, $relationsAutorisees));
    }

    private function tracer(string $action, Reservation $reservation, ?array $avant = null, ?array $apres = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'reservations',
            'entity_id' => $reservation->id,
            'entity_label' => $reservation->client_nom.' '.$reservation->client_prenom.' — '.$reservation->terrain_titre,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
