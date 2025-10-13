<?php

// app/Models/Application.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'property_id',
        'applicant_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function property(){ return $this->belongsTo(Property::class); }
    public function applicant(){ return $this->belongsTo(User::class, 'applicant_id'); }
}
