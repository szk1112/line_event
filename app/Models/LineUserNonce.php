<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LineUserNonce
 *
 * @property int $id
 * @property string $nonce
 */
class LineUserNonce extends Model
{

    protected $table = 'line_user_nonce';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'nonce',
    ];


}
