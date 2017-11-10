<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Account;

class StatsController extends Controller
{
    public function getBalance($account = null, $date = null)
    {
        if(!$account || $account == 'all') {
            $balance = Auth::user()->getBalance($date);
        } else {
            $account = Auth::user()->getAccount($account);
            if(!$account) {
                return response()->json([
                    'message' => "Account not found."
                ], 404);
            }
            $balance = $account->getBalance($date);
        }

        return response()->json(['balance' => $balance]);
    }

    public function getIncome($year = null, $month = null, $day = null)
    {
        $income = Auth::user()->getIncome($year, $month, $day);

        return response()->json(['income' => $income]);
    }

    public function getExpense($year = null, $month = null, $day = null)
    {
        $expense = Auth::user()->getExpense($year, $month, $day);

        return response()->json(['expense' => $expense]);
    }
}
