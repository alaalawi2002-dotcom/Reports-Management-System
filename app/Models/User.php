<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * الحقول التي يمكن تعبئتها تلقائيًا
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        
    ];

    /**
     * الحقول المخفية عند الإرجاع (JSON)
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * تحويلات الحقول
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * علاقة: المستخدم عنده بلاغات كثيرة
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * هل المستخدم مدير؟
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * هل المستخدم مدير قسم؟
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }



    public function department(){
           return $this->belongsTo(Department::class);

    }
}