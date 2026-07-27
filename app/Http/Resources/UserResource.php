<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            
            'contact' => [
                'phone' => $this->phone,
            ],
            
            'professional' => [
                'department' => $this->department,
                'position' => $this->position,
            ],
            
            'status' => [
                'is_active' => $this->is_active,
                'can_login' => $this->can_login,
                'last_login' => $this->last_login_at?->toISOString(),
            ],
            
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            
            'permissions' => $this->whenLoaded('roles', function () {
                return $this->getAllPermissions()->pluck('name');
            }),
            
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}