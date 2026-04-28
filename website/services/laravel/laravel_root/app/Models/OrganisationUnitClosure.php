<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationUnitClosure extends Model
{
    protected $table = 'organisation_unit_closure';

    public $timestamps = false;

    protected $hidden = ['id'];

    protected $fillable = ['ancestor_id', 'descendant_id', 'depth'];
}
