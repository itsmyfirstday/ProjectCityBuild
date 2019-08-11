<?php

namespace App\Entities\Accounts\Models;

use App\Model;
use Illuminate\Support\Facades\URL;

final class AccountPasswordReset extends Model
{
    protected $table = 'account_password_resets';

    protected $primaryKey = 'email';
    
    public $incrementing = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];

    protected $hidden = [
    ];

    protected $dates = [
        'created_at',
    ];

    public $timestamps = false;

    public function getPasswordResetUrl()
    {
        return URL::temporarySignedRoute('front.password-reset.edit', now()->addMinutes(20), [
            'token' => $this->token,
        ]);
    }
}
