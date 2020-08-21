<?php

namespace App\Entities\Accounts\Models;

use App\Model;

/**
 * Class AccountCustomer
 * @package App\Entities\Accounts\Models
 *
 * Represents a unique Customer in Stripe - a way to identify a unique user who
 * has made a transaction via Stripe
 */
final class AccountCustomer extends Model
{
    protected $table = 'account_customers';

    protected $primaryKey = 'account_customer_id';

    protected $fillable = [
        'account_id',
        'customer_id',
    ];

    protected $hidden = [
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'account_id');
    }
}
