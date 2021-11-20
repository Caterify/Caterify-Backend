<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'date',
        'price'
    ];

    protected $hidden = [
        'menu_id',
        'created_at',
        'updated_at',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'schedule_id');
    }
}
