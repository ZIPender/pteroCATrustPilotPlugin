<?php

namespace Plugins\TrustpilotReview\Models;

use Illuminate\Database\Eloquent\Model;

class TrustpilotDismissal extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trustpilot_dismissals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'server_id',
        'dismissed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'dismissed_at' => 'datetime',
    ];
}
