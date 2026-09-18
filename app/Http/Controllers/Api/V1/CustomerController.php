<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\SubscriptionService;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly DashboardCacheService $dashboardCacheService
    ) {}

    public function index(Request $request)
    {
        $query = Customer::query()
            ->where('company_id', $request->user()->company_id);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $customers = $query
            ->latest('id')
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request)
    {
        $company = $request->user()->company;

        $currentUsage = $company->customers()->count();

        $this->subscriptionService->ensureWithinLimit(
            $company,
            'customers',
            $currentUsage
        );

        $customer = Customer::create([
            'company_id' => $company->id,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'status' => $request->validated('status'),
        ]);

        $this->dashboardCacheService->forget($company->id);

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $customer)
    {
        $customer = $this->findCompanyCustomer(
            $request,
            $customer
        );

        return new CustomerResource($customer);
    }

    public function update(
        UpdateCustomerRequest $request,
        int $customer
    ) {
        $customer = $this->findCompanyCustomer(
            $request,
            $customer
        );

        $customer->update($request->validated());
        $this->dashboardCacheService->forget($customer->company_id);

        return new CustomerResource($customer->fresh());
    }

    public function destroy(Request $request, int $customer)
    {
        $customer = $this->findCompanyCustomer(
            $request,
            $customer
        );

        $customer->delete();
        $this->dashboardCacheService->forget($customer->company_id);

        return response()->json([
            'message' => 'Customer deleted successfully.',
        ]);
    }

    private function findCompanyCustomer(
        Request $request,
        int $customerId
    ): Customer {
        return Customer::query()
            ->where('company_id', $request->user()->company_id)
            ->findOrFail($customerId);
    }
}
