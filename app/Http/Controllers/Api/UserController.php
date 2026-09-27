<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Liste paginée, avec recherche simple (?q=) et filtre actif (?actif=1).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $query = User::query();

        if ($request->filled('q')) {
            $terme = $request->string('q');
            $query->where(function ($q) use ($terme) {
                $q->where('nom', 'ilike', "%{$terme}%")
                  ->orWhere('prenom', 'ilike', "%{$terme}%")
                  ->orWhere('email', 'ilike', "%{$terme}%");
            });
        }

        if ($request->has('actif')) {
            $query->where('actif', $request->boolean('actif'));
        }

        $users = $query->orderBy('nom')->paginate($request->integer('per_page', 15));

        return UserResource::collection($users)->response();
    }

    /**
     * POST /api/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['actif'] = $data['actif'] ?? true;
        $data['role'] = $data['role'] ?? 'admin';

        $user = User::create($data);

        $this->tracer('users.create', $user);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        return (new UserResource($user))->response();
    }

    /**
     * PUT/PATCH /api/users/{user}
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $data = $request->validated();
        $avant = $user->only(array_keys($data));

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        $this->tracer('users.update', $user, $avant, $user->only(array_keys($data)));

        return (new UserResource($user))->response();
    }

    /**
     * DELETE /api/users/{user}
     * Suppression logique (soft delete) : le compte n'est jamais supprimé physiquement.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $user->update(['actif' => false]);
        $user->delete();

        $this->tracer('users.delete', $user);

        return response()->json(['message' => 'Utilisateur désactivé et supprimé (soft delete).']);
    }

    /**
     * POST /api/users/{id}/restore
     * Restaure un utilisateur préalablement supprimé (soft delete).
     */
    public function restore(int $id): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        $user->update(['actif' => true]);

        $this->tracer('users.restore', $user);

        return (new UserResource($user))->response();
    }

    /**
     * Petite aide pour tracer l'action dans audit_logs (append-only).
     */
    private function tracer(string $action, User $user, ?array $avant = null, ?array $apres = null): void
    {
        $this->authorize('viewAny', User::class);
        AuditLog::create([
            'user_id' => auth()->id(), // administrateur ayant réalisé l'action
            'user_name' => auth()->user()?->nom.' '.auth()->user()?->prenom,
            'action' => $action,
            'entity_type' => 'users',
            'entity_id' => $user->id,
            'entity_label' => $user->nom.' '.$user->prenom,
            'old_values' => $avant,
            'new_values' => $apres,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
