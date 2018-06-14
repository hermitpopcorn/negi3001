<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use App\Transaction;

class Account extends Model
{
    use Eloquence, Mappable;

    protected $fillable = [
        'uid', 'user_id', 'name', 'initial_balance', 'type'
    ];

    protected $hidden = [
         'created_at', 'updated_at',
         'user_id', 'id', 'initial_balance'
    ];

    protected $maps = ['initialBalance' => 'initial_balance'];
    protected $appends = ['initialBalance'];

    public function user()
    {
        return $this->belongsTo('\App\User');
    }

    public function transactions()
    {
        return $this->hasMany('\App\Transaction');
    }

    public function transfers()
    {
        return $this->hasMany('\App\Transaction', 'target_id')->where('type', 'x');
    }

    public static function createDefaultAccountForUser($user)
    {
        self::store($user, "Bank", 0, 'regular');
    }

    public static function store($user, $name, $initialBalance = 0, $type = 'regular', $ordering = 0)
    {
        $account = new self();

        // Look for unique UID
        $unique = false;
        while(!$unique) {
            $uid = str_random(8);
            $existing = self::where('uid', $uid)->first();
            if(!$existing) {
                $unique = true;
            }
        }

        // Insert
        $account->user_id = $user->id;
        $account->uid = $uid;
        $account->name = $name;
        $account->initial_balance = $initialBalance;
        $account->type = $type;
        $account->ordering = $ordering;
        if($account->save()) {
            return $account;
        } else {
            return false;
        }
    }

    public function patch($newData)
    {
        $this->name = $newData['name'] ?: "Account";
        $this->initial_balance = $newData['initialBalance'] ?: 0;
        $this->type = $newData['type'];
        $this->ordering = $newData['ordering'];

        return $this->save();
    }

    public function getBalance($uptilDate = null)
    {
        if($uptilDate) {
            $split = explode(" ", $uptilDate);

            // if time is not specified, add 23:59:59 so that
            // the transactions in the specified day is counted for
            if(count($split) < 2) {
                $uptilDate = "{$uptilDate} 23:59:59";
            }
        }

        // Calculate transactions
        $balance = $this->initial_balance;
        if(!$uptilDate) {
            $transactions = $this->transactions;
        } else {
            $transactions = $this->transactions()->where('date', '<=', $uptilDate)->get();
        }
        foreach($transactions as $transaction) {
            if($transaction->type === 'i') {
                $balance += $transaction->amount;
            } else
            if($transaction->type === 'e' || $transaction->type === 'x') {
                $balance -= $transaction->amount;
            }
        }

        // Calculate transfers
        if(!$uptilDate) {
            $transfers = $this->transfers()->whereHas('account')->get();
        } else {
            $transfers = $this->transfers()->whereHas('account')->where('date', '<=', $uptilDate)->get();
        }
        foreach($transfers as $transfer) {
            $balance += $transfer->amount;
        }

        return $balance;
    }

    private function calcTransactionsAndTransfersTotalAmount($mode, $year = null, $month = null, $day = null)
    {
        if($mode !== 'i' && $mode !== 'e') {
            return false;
        }

        $total = 0;

        // Calculate transactions
        $transactions = $this->transactions();
        $transactions = Transaction::queryWhereBetweenDate($transactions, $year, $month, $day);
        // If counting income
        if($mode === 'i') {
            $transactions = $transactions->where('type', 'i')->get();
        } else
        // If counting expense
        if($mode === 'e') {
            // Get transactions that are either:
            // an expense, or
            // a transfer TO an account that is marked as money sink
            $accountID = $this->id; // fix for eloquent's confused behavior (1)
            $transactions = $transactions
                ->where('type', 'e')
                ->orWhere(function($q) use (&$accountID, &$year, &$month, &$day) {
                    $q = $q->where('account_id', $accountID); // fix.. (2)
                    $q = Transaction::queryWhereBetweenDate($q, $year, $month, $day); // fix.. (3)
                    return $q
                        ->where('account_id', $accountID)
                        ->where('type', 'x')
                        ->where(function($q2) {
                            $q2->whereHas('target', function($q3) {
                                    $q3->where('type', 'sink');
                                })
                                ->orDoesntHave('target')
                            ;
                        })
                    ;
                })
                ->get()
            ;
        }

        // Sum total amount
        foreach($transactions as $transaction) {
            $total += $transaction->amount;
        }

        // If counting income, include transfers FROM accounts marked as sink
        if($mode === 'i') {
            $transfers = $this->transfers();
            $transfers = Transaction::queryWhereBetweenDate($transfers, $year, $month, $day);
            $transfers = $transfers->whereHas('account', function($q) {
                $q->where('type', 'sink');
            })->get();

            // Sum total amount from transfers
            foreach($transfers as $transfer) {
                $total += $transfer->amount;
            }
        }

        return $total;
    }

    public function getIncome($year = null, $month = null, $day = null)
    {
        return $this->calcTransactionsAndTransfersTotalAmount('i', $year, $month, $day);
    }

    public function getExpense($year = null, $month = null, $day = null)
    {
        return $this->calcTransactionsAndTransfersTotalAmount('e', $year, $month, $day);
    }

    public static function remove($user, $accountUID)
    {
        $target = self::where('uid', $accountUID)->first();
        if(!$target) { return false; }

        if(intval($user->id) === intval($target->user_id)) {
            foreach($target->transactions as $transaction) {
                $transaction->tags()->detach();
                $transaction->delete();
            }
            return $target->delete();
        } else {
            return false;
        }
    }
}
