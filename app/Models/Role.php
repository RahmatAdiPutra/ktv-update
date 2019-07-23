<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function scopeHasAccess($query)
    {
        $idHasAccess = DB::table('role_has_permission')->select('role_id')
            ->groupBy('role_id')
            ->pluck('role_id')
            ->toArray();
        
        return $query->whereIn('role_id', $idHasAccess);
    }

    public function hasPermissionTo($app)
    {
        return 0 < $this->permissions()
            ->where('code', 'like', $app . '.%')
            ->count();
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permission', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
