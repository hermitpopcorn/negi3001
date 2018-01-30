<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'uid', 'account_id', 'target_id', 'type', 'amount', 'note', 'date'
    ];

    protected $hidden = [
         'created_at', 'updated_at',
         'id', 'user_id', 'account_id', 'target_id',
         'pivot'
    ];

    public function account()
    {
        return $this->belongsTo('\App\Account', 'account_id');
    }

    public function target()
    {
        return $this->belongsTo('\App\Account', 'target_id');
    }

    public function tags()
    {
        return $this->belongsToMany('\App\Tag', 'transactions__tags');
    }

    public static function queryWhereBetweenDate($query, $year = null, $month = null, $day = null)
    {
        if($year && !$month) {
            $query = $query->whereBetween('date', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"]);
        } else
        if($year && $month && !$day) {
            $query = $query->whereBetween('date', ["{$year}-{$month}-01 00:00:00", date("Y-m-t 23:59:59", strtotime("{$year}-{$month}-01"))]);
        } else
        if($year && $month && $day) {
            $query = $query->whereBetween('date', ["{$year}-{$month}-{$day} 00:00:00", "{$year}-{$month}-{$day} 23:59:59"]);
        }

        return $query;
    }

    public static function fetch($from, $year = null, $month = null, $day = null, $tag = null)
    {
        $q = self::with('account')->with('target')->with('tags');

        $type = get_class($from);
        if($type == "App\User") {
            $q = $q->whereHas('account', function($q2) use (&$from) {
                $q2->where('user_id', $from->id);
            });
        } else
        if($type == "App\Account") {
            $q = $q->where('account_id', $from->id);
        }

        $q = self::queryWhereBetweenDate($q, $year, $month, $day);

        if($tag) {
            $q->whereHas('tags', function($q2) use (&$tag) {
                $q2->where('name', $tag);
            });
        }

        $q = $q->orderBy('date', 'desc');
        return $q->get();
    }

    public static function search($user,
        $accountUID = null, $type = null, $amount = 0, $equality = '=',
        $date = null, $dateRange = '=', $note = null, $tags = null,
        $orderBy = 'date', $orderArrangement = 'desc'
    )
    {
        $q = self::with('account')->with('target')->with('tags');

        if($accountUID) {
            $q = $q->where(function($q2) use (&$accountUID) {
                $q2 = $q2->whereHas('account', function($q3) use (&$accountUID) {
                    $q3->where('uid', $accountUID);
                })->orWhereHas('target', function($q3) use (&$accountUID) {
                    $q3->where('uid', $accountUID);
                });
            });
        } else {
            $q = $q->whereHas('account', function($q2) use (&$user) {
                $q2->where('user_id', $user->id);
            });
        }

        if($type) {
            if($type == "income") { $type = "i"; }
            if($type == "expense") { $type = "e"; }
            if($type == "transfer") { $type = "x"; }
            $q = $q->where('type', $type);
        }

        if($amount > 0) {
            $q = $q->where('amount', $equality, $amount);
        }

        if($date) {
            // check if with time
            $date = explode(" ", $date);
            if(count($date) == 2) {
                // with time
                $q = $q->where('date', $dateRange, $date[0] . " " . $date[1]);
            } else {
                // just the date
                $date = $date[0];
                if($dateRange == '=') {
                    $q = $q->whereBetween('date', ["{$date} 00:00:00", "{$date} 23:59:59"]);
                } else
                if($dateRange == '>=' || $dateRange == '<') {
                    $q = $q->where('date', $dateRange, "{$date} 00:00:00");
                } else
                if($dateRange == '>' || $dateRange == '<=') {
                    $q = $q->where('date', $dateRange, "{$date} 23:59:59");
                } else
                if($dateRange == '<>') {
                    $q = $q->whereNotBetween('date', ["{$date} 00:00:00", "{$date} 23:59:59"]);
                }
            }
        }

        if($note) {
            $q = $q->whereRaw("MATCH(note) AGAINST (?)", [$note]);
        }

        if($tags) {
            foreach($tags as $tag) {
                $q->whereHas('tags', function($q2) use (&$tag) {
                    $q2->where('name', $tag);
                });
            }
        }

        $q = $q->orderBy($orderBy, $orderArrangement);
        return $q->get();
    }

    public static function store($user, $account, $target, $type, $amount, $note, $date)
    {
        $new = new self();

        $unique = false;
        while(!$unique) {
            $uid = str_random(8);
            $existing = self::where('uid', $uid)->first();
            if(!$existing) {
                $unique = true;
            }
        }

        $new->uid = $uid;
        $new->account_id = $account->id;
        $new->target_id = $target != null ? $target->id : null;
        $new->type = $type;
        $new->amount = $amount ? $amount : "0";
        $new->note = $note ? $note : "";
        $new->date = $date;
        $save = $new->save();

        if(!$save) {
            return false;
        }

        return $new;
    }

    public function patch($newData)
    {
        $this->account_id = $newData['account']->id;
        $this->target_id = $newData['target'] ? $newData['target']->id : null;
        $this->type = $newData['type'];
        $this->amount = $newData['amount'] ?: 0;
        $this->note = $newData['note'] ?: "";
        $this->date = $newData['date'];

        return $this->save();
    }

    public function setTags($tags)
    {
        // Remove all previous tags
        $this->tags()->detach();

        // Attach tags one by one
        foreach($tags as $tag) {
            $this->tags()->attach($tag, ['user_id' => $this->account->user_id]);
        }

        return true;
    }

    public static function remove($user, $transactionUID)
    {
        $target = self::where('uid', $transactionUID)->first();
        if(!$target) { return false; }

        if(intval($user->id) === intval($target->account->user_id)) {
            $target->tags()->detach();
            return $target->delete();
        } else {
            return false;
        }
    }
}
