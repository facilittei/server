<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class GroupInvite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'name',
        'email',
        'token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Generate the token that identifies the invite.
     *
     * @param  int  $group_id
     * @return string
     */
    public function generateToken($group_id)
    {
        if ($group_id) {
            return Crypt::encryptString($group_id.'-'.Str::uuid());
        }
    }

    /**
     * Validate and decrypt the identification token invite.
     *
     * @param  string  $token
     * @return array
     */
    public function identifyToken($token)
    {
        if ($token) {
            try {
                $invite = Crypt::decryptString($token);
                $components = explode('-', $invite);

                return [
                    'group_id' => array_shift($components),
                ];
            } catch (DecryptException $e) {
                return [
                    'error' => $e,
                ];
            }
        }
    }
}
