<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'method',
        'uri',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_route_permission')
            ->withTimestamps();
    }
}
