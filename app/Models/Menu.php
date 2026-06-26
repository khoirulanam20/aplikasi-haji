<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use LogsModelActivity;

    protected $fillable = [
        'domain',
        'label',
        'route_name',
        'icon',
        'sort_order',
        'permission_name',
    ];

    public function resolvedUrl(): string
    {
        $routeName = resolve_admin_route_name($this->route_name);

        if ($routeName === null) {
            return '#';
        }

        try {
            return route($routeName, [], false);
        } catch (\Throwable) {
            return '#';
        }
    }
}
