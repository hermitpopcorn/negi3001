<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'api_token'
    ];

    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at'
    ];

    public function accounts()
    {
        return $this->hasMany('\App\Account');
    }

    public function transactions()
    {
        return $this->hasManyThrough('App\Transaction', 'App\Account');
    }

    public static function getByToken($token)
    {
        return self::where('api_token', $token)->first();
    }

    public function getBalance($uptilDate = null)
    {
        $balance = 0;

        $accounts = $this->accounts()->where('is_sink', false)->get();
        foreach($accounts as $account) {
            $balance += $account->getBalance($uptilDate);
        }
        return $balance;
    }

    public function getAccount($accountUID)
    {
        return $this->accounts()->where('uid', $accountUID)->first();
    }

    private function calcIncomeOrTransfer($mode, $year = null, $month = null, $day = null)
    {
        $total = 0;
        $accounts = $this->accounts()->where('is_sink', 0)->get();

        foreach($accounts as $account) {
            if($mode === 'i') {
                $total += $account->getIncome($year, $month, $day);
            } else
            if($mode === 'e') {
                $total += $account->getExpense($year, $month, $day);
            }
        }

        return $total;
    }

    public function getIncome($year = null, $month = null, $day = null)
    {
        return $this->calcIncomeOrTransfer('i', $year, $month, $day);
    }

    public function getExpense($year = null, $month = null, $day = null)
    {
        return $this->calcIncomeOrTransfer('e', $year, $month, $day);
    }
}
