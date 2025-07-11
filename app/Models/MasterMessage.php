<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMessage extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_messages';
    protected $fillable = [
        'name',
        'email',
        'message',
    ];
}
