<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Transaction;
use App\Tag;

class TransactionController extends Controller
{
    public function fetch(Request $request)
    {
        if($request->input('search')) {
            return $this->search($request);
        }

        $transactions = Transaction::fetch(
            Auth::user(),
            $request->input('year'),
            $request->input('month'),
            $request->input('day'),
            $request->input('tag')
        );

        return response()->json(['transactions' => $transactions]);
    }

    private function search(Request $request)
    {
        $transactions = Transaction::search(
            Auth::user(),
            $request->input('account'),
            $request->input('type'),
            $request->input('amount'),
            $request->input('equality'),
            $request->input('date'),
            $request->input('dateRange'),
            $request->input('note'),
            $request->input('tags'),
            $request->input('orderBy'),
            $request->input('orderArrangement')
        );

        return response()->json(['transactions' => $transactions]);
    }

    public function show($transactionUID)
    {
        $transaction = Transaction::with('account')->with('target')->with('tags')->where('uid', $transactionUID)->first();
        if($transaction) { if(intval($transaction->account->user_id) === intval(Auth::user()->id)) {
            return response()->json(['transaction' => $transaction]);
        }}

        return response()->json(['message' => "Transaction not found."], 404);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type');
        $accountUID = $request->input('account');
        $targetUID = $type === 'x' ? $request->input('target') : null;
        $amount = $request->input('amount');
        $note = $request->input('note');
        $date = $request->input('date');
        $tags = $request->input('tags');

        $insert = false;

        // Verify user and account
        $account = Auth::user()->accounts()->where('uid', $accountUID)->first();
        if($targetUID) {
            $target = Auth::user()->accounts()->where('uid', $targetUID)->first();
        } else {
            $target = null;
        }

        if($account && ($target || $type !== 'x')) {
            $newTransaction = Transaction::store($user, $account, $target, $type, $amount, $note, $date);
            if($newTransaction) {
                $tagObjects = [];
                foreach($tags as $tag) {
                    $newTag = Tag::store($tag);
                    if($newTag) { $tagObjects[] = $newTag; }
                }

                $newTransaction->setTags($tagObjects);

                $insert = true;
            }
        }

        if($insert) {
            return response()->json([
                'transaction' => $newTransaction
            ], 200);
        } else {
            return response()->json([
                'message' => "Invalid input."
            ], 400);
        }
    }

    public function patch(Request $request, $transactionUID)
    {
        $type = $request->input('type');
        $accountUID = $request->input('account');
        $targetUID = $type === 'x' ? $request->input('target') : null;
        $amount = $request->input('amount');
        $note = $request->input('note');
        $date = $request->input('date');
        $tags = $request->input('tags');

        $update = false;

        // Verify user and account
        $account = Auth::user()->accounts()->where('uid', $accountUID)->first();
        if($targetUID) {
            $target = Auth::user()->accounts()->where('uid', $targetUID)->first();
        } else {
            $target = null;
        }

        if($account && ($target || $type !== 'x')) {
            $transaction = Transaction::where('uid', $transactionUID)->first();
            if($transaction) { if($transaction->account->user_id == Auth::user()->id) {
                $update = $transaction->patch([
                    'account' => $account,
                    'target' => $target,
                    'type' => $type,
                    'amount' => $amount,
                    'note' => $note,
                    'date' => $date
                ]);
            }}
            if($update) {
                $tagObjects = [];
                foreach($tags as $tag) {
                    $newTag = Tag::store($tag);
                    if($newTag) { $tagObjects[] = $newTag; }
                }

                $transaction->setTags($tagObjects);

                $update = true;
            }
        }

        if($update) {
            return response()->json([
                'transaction' => $transaction
            ], 200);
        } else {
            return response()->json([
                'message' => "Invalid input."
            ], 400);
        }
    }

    public function remove($transaction)
    {
        if(Transaction::remove(Auth::user(), $transaction)) {
            return response()->json(['message' => "Transaction deleted."]);
        } else {
            return response()->json(['message' => "Transaction not found."], 404);
        }
    }
}
