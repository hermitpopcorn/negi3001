<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Account;

class AccountController extends Controller
{
    public function show($accountUID)
    {
        $account = Auth::user()->accounts()->where('uid', $accountUID)->first();
        if(!$account) {
            return response()->json(['message' => "Account not found."], 404);
        }

        return response()->json(['account' => $account->toArray()]);
    }

    public function fetch()
    {
        $accounts = Auth::user()->accounts()->get();

        return response()->json(['accounts' => $accounts->toArray()]);
    }

    public function store(Request $request)
    {
        $name = $request->input('name');
        $initialBalance = $request->input('initialBalance');
        $isSink = $request->input('isSink');

        $insert = false;

        // Insert acount
        $newAccount = Account::store(Auth::user(), $name, $initialBalance, (boolean) $isSink);

        if($newAccount) {
            return response()->json([
                'account' => $insert,
                'message' => "Account created."
            ], 200);
        } else {
            return response()->json([
                'message' => "Invalid input."
            ], 400);
        }
    }

    public function patch(Request $request, $accountUID)
    {
        $name = $request->input('name');
        $initialBalance = $request->input('initialBalance');
        $isSink = $request->input('isSink');

        $update = false;

        // Verify user and account
        $account = Account::where('uid', $accountUID)->first();
        if(!$account) {
            return response()->json(['message' => "Account not found."], 404);
        } else if($account->user_id !== Auth::user()->id) {
            return response()->json(['message' => "Account not found."], 404);
        }

        $update = $account->patch([
            'name' => $name,
            'initialBalance' => $initialBalance,
            'isSink' => (boolean) $isSink
        ]);

        if($update) {
            return response()->json([
                'account' => $account,
                'message' => "Account updated."
            ], 200);
        } else {
            return response()->json([
                'message' => "Invalid input."
            ], 400);
        }
    }

    public function remove($uid)
    {
        if(Account::remove(Auth::user(), $uid)) {
            return response()->json(['message' => "Account deleted."]);
        } else {
            return response()->json(['message' => "Account not found."], 404);
        }
    }
}
