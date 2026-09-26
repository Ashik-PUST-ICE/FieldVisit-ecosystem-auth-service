<?php

namespace App\Http\Resources\Modules\Employees;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'image' => isset($this->image) ? asset($this->image) : null,
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                });
            }),
            'department' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->department?->id,
                    'name' => $this->employee->department?->name,
                ];
            }),
            'designation' => $this->whenLoaded('employee', function () {
                return [
                    'id' => $this->employee->designation?->id,
                    'name' => $this->employee->designation?->name,
                ];
            }),
            'status' => $this->status?->boolValue(),
            'unique_id' => $this->unique_id,
            'formatted_status' => $this->status?->label(),
            'created_at' => $this->created_at?->format('F d,Y h:i A')
        ];
    }
}
