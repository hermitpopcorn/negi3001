<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\Account;

class UserController extends Controller
{
    protected function createUser($email, $name, $password)
    {
        $user = null;
        $tries = 0;
        while(!$user) {
            try {
                $user = User::create([
                    'email' => $email,
                    'name' => $name,
                    'password' => bcrypt($password),
                    'api_token' => str_random(24)
                ]);
            } catch(\PDOException $e) {
                if($e->getCode() == "23000") {
                    if($tries < 5) {
                        $tries++;
                    } else {
                        throw new \Exception("Token conflict: too many tries.", 500);
                    }
                } else {
                    throw $e;
                    break;
                }
            } catch(\Exception $e) {
                throw $e;
                break;
            }
        }

        if($user) {
            Account::createDefaultAccountForUser($user);
        }

        return $user;
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|max:255|email|unique:users',
            'name' => 'sometimes|required|string|max:255|nullable',
            'password' => 'required|confirmed|string|min:6'
        ]);

        try {
            $user = $this->createUser($request->input('email'), $request->input('name'), $request->input('password'));
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "New account created successfully."], 200);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if(!$user) {
            return response()->json(['message' => "Wrong email/password."], 401);
        }

        if(Hash::check($request->input('password'), $user->password)) {
            return response()->json(['token' => $user->api_token, 'message' => "Login successful."], 200);
        } else {
            return response()->json(['message' => "Wrong email/password."], 401);
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'name' => $user->name,
            'email' => $user->email
        ], 200);
    }
}
