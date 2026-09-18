<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = DB::transaction(function () use ($validated) {

            $company = Company::create([
                'name' => $validated['company_name'],
                'slug' => \Illuminate\Support\Str::slug(
                    $validated['company_name']
                ) . '-' . \Illuminate\Support\Str::lower(
                    \Illuminate\Support\Str::random(6)
                ),
                'email' => $validated['email'],
                'status' => 'active',
            ]);

            $user = User::create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $freePlan = Plan::where('slug', 'free')
                ->where('is_active', true)
                ->firstOrFail();

            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            $user->assignRole('owner');

            $token = $user->createToken('api-token')->plainTextToken;

            return [
                'company' => $company,
                'user' => $user,
                'token' => $token,
            ];
        });

        return response()->json([
            'message' => 'Registration successful.',
            'data' => $result,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->company->status !== 'active') {
            return response()->json([
                'message' => 'Your company account is inactive.',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'user' => $user->load('company'),
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('company');

        return response()->json([
            'data' => [
                'user' => $user,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }
}
