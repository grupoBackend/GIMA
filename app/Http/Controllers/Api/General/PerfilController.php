<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerfilController extends Controller
{
    /**
     * Show
     */
    public function show()
    {
        $user = Auth::user()->load('roles');
        return new UserResource($user);
    }

    /**
     * Update
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8|confirmed',
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return new UserResource($user->load('roles'));
    }

    /**
     * Delete
     */
    public function destroy()
    {
        $user = Auth::user();

        $user->update([
            'telefono' => null,
            'recovery_pin' => null,
        ]);

        return response()->json([
            'message' => 'Datos eliminados correctamente',
        ]);
    }
}