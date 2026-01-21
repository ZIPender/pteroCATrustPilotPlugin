<?php

namespace Plugins\TrustpilotReview\Models;

use Illuminate\Database\Eloquent\Model;

class TrustpilotSetting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trustpilot_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
    ];
}
