<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  //
  public function signup(Request $request)
  {
    $validated = $request->validate([
      'name' => [
        'bail',
        'required',
        'string',
        'max:255',
        "regex:/^(?=.*\p{L})[\p{L}\p{N} '\-\.\,]+$/u"
      ],
      'email' => ['bail', 'required', 'email', 'max:255', 'unique:users'],
      'password' => ['required', 'string', 'min:10', 'confirmed']
    ]);

    $user = User::create([
      'name' => $validated['name'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password'])
    ]);

    // Auth::login($user);

    return response()->json([
      'message' => 'User created successfully',
      'user' => $user
    ], 201);
  }
}
