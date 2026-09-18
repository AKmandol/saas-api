<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWelcomeEmail;

class UserController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly DashboardCacheService $dashboardCacheService
    ) {}

    public function index(Request $request)
    {
        $query = User::query()
            ->where('company_id', $request->user()->company_id)
            ->with('roles');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->latest('id')
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $company = $request->user()->company;

        $currentUsage = $company->users()->count();

        $this->subscriptionService->ensureWithinLimit(
            $company,
            'users',
            $currentUsage
        );

        $user = DB::transaction(function () use ($request, $company) {

            $user = User::create([
                'company_id' => $company->id,
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]);

            $user->assignRole($request->validated('role'));

            return $user;
        });

        SendWelcomeEmail::dispatch($user->id)->afterCommit();
        $this->dashboardCacheService->forget($company->id);

        return (new UserResource($user->load('roles')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $user)
    {
        $user = $this->findCompanyUser(
            $request,
            $user
        );

        return new UserResource($user->load('roles'));
    }

    public function update(
        UpdateUserRequest $request,
        int $user
    ) {
        $user = $this->findCompanyUser(
            $request,
            $user
        );

        $data = $request->validated();

        if (array_key_exists('password', $data)) {
            if (blank($data['password'])) {
                unset($data['password']);
            }
        }

        $role = $data['role'] ?? null;

        unset($data['role']);

        DB::transaction(function () use (
            $user,
            $data,
            $role
        ) {
            $user->update($data);

            if ($role !== null) {
                $user->syncRoles([$role]);
            }
        });

        $this->dashboardCacheService->forget($user->company_id);

        return new UserResource(
            $user->fresh()->load('roles')
        );
    }

    public function destroy(Request $request, int $user)
    {
        $user = $this->findCompanyUser(
            $request,
            $user
        );

        // Prevent deleting yourself.
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        // Prevent deleting the company owner.
        if ($user->hasRole('owner')) {
            return response()->json([
                'message' => 'The company owner cannot be deleted.',
            ], 422);
        }

        $user->delete();
        $this->dashboardCacheService->forget($user->company_id);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    private function findCompanyUser(
        Request $request,
        int $userId
    ): User {
        return User::query()
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($userId);
    }
}
