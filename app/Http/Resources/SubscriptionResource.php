<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $plan = $this->plan;

        return [
            'id' => $this->id,

            'status' => $this->status,

            'starts_at' => $this->starts_at?->toISOString(),

            'ends_at' => $this->ends_at?->toISOString(),

            'plan' => [
                'id' => $plan?->id,
                'name' => $plan?->name,
                'slug' => $plan?->slug,
            ],

            'features' => $plan?->features
                ->map(function ($feature) {
                    return [
                        'feature' => $feature->feature,
                        'limit' => $feature->limit,
                    ];
                })
                ->values(),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
