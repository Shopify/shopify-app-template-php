<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Session extends Authenticatable
{
    use HasFactory;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['access_token'];

    /**
     * Disable "remember me" compatibility.
     *
     * @var null
     */
    protected $rememberTokenName = null;

    /**
     * The non-primary key value that is used to identify a unique session.
     *
     * Normally, for a user User model, this would be something like "email",
     * however, the equivalent for sessions is the session_id column.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'session_id';
    }

    /**
     * Sessions do not have passwords, they have access tokens instead.
     *
     * This is the functional equivalent of a password in the context of
     * Shopify sessions.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return 'access_token';
    }
}
