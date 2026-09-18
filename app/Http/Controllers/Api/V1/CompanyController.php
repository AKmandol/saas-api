<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function show(Request $request)
    {
        $company = $request->user()->company;

        return new CompanyResource($company);
    }

    public function update(UpdateCompanyRequest $request)
    {
        $company = $request->user()->company;

        $company->update(
            $request->validated()
        );

        return new CompanyResource(
            $company->fresh()
        );
    }
}
