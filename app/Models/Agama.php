<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    public $incrementing = false;
	protected $table = 'ref.agama';
	protected $primaryKey = 'agama_id';
    protected $guarded = [];
}
