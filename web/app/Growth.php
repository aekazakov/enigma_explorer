<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Growth extends Model {
    protected $table = 'GrowthPlate';
    protected $primaryKey = 'growthPlateId';
    public $timestamps = false;    // absense of created_at and updated_at columns
}
