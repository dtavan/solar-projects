<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolarProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'system_size',
        'system_details',
        'site_latitude',
        'site_longitude',
    ];

    public function __construct($attrs = [])
    {
        parent::__construct($attrs);
        $this->uuid = Uuid::uuid4()->toString();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class);
    }
}
