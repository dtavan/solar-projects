<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
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
}
