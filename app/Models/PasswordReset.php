<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Attributes
 * @property int id
 * @property string email
 * @property int country_id
 * @property string phone_no
 * @property string code
 * @property string token
 * @property boolean is_active
 *
 **/
class PasswordReset extends Model
{
    use HasFactory;
        
    /** Configuration & Metadata */

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'email',
        'code',
        'country_id',
        'phone_no',
        'token'
    ];
   
}
