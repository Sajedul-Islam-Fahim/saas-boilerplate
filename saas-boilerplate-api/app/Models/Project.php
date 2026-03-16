<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'description',
        'status',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getTaskCountsAttribute(): array
    {
        return [
            'total'       => $this->tasks()->count(),
            'todo'        => $this->tasks()->where('status', 'todo')->count(),
            'in_progress' => $this->tasks()->where('status', 'in_progress')->count(),
            'review'      => $this->tasks()->where('status', 'review')->count(),
            'done'        => $this->tasks()->where('status', 'done')->count(),
        ];
    }
}
