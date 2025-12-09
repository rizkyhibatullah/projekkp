<?php

namespace App\Models\keamanan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\keamanan\Role;

class Member extends Authenticatable
{
    use HasFactory, Notifiable,HasRoles;
    protected $table = 'm_members';
    protected $primaryKey = 'Mem_Auto';

    public $timestamps = false;

    protected $fillable = [
        'Mem_ID', 'Mem_UserName', 'mem_password', 'Mem_ActiveYN', 'role_id',
        'mem_veriterima', 'mem_veriretur', 'Mem_UpdateID', 'Mem_LastUpdate',
        'Mem_StartPeriod', 'Mem_EndPeriod', 'Mem_Menu', 'Mem_Note', 'mem_count', 'Mem_rECORD','warehouse_access',
    ];

    protected $hidden = [
        'mem_password',
    ];

    protected $casts = [
        'warehouse_access' => 'array',
    ];

    public function getAuthIdentifierName()
    {
        return 'Mem_Auto';
    }

    public function getNameAttribute()
    {
        return $this->Mem_UserName;
    }

    // Override method getName dari Authenticatable
    public function getName()
    {
        return $this->Mem_UserName;
    }

    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    public function getAuthPassword()
    {
        return $this->mem_password;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {

    }

    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\keamanan\Role::class, 'role_id', 'id');
    }

    public function rightAccesses()
    {
        return $this->hasMany(\App\Models\keamanan\RightAccess::class, 'AC_USER', 'Mem_ID');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\Presensi\Employee::class, 'emp_Code', 'Mem_ID');
    }
}
