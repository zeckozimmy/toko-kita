<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 'name' => 'required|string|max:255',
            // 'email' => 'required|email|unique:users,email',
            // 'password' => 'required|string|min:6|confirmed',
            // 'alamat' => 'required|string|max:255',
            // 'alamat2' => 'nullable|string|max:255',
            // 'tlp' => 'required|string|max:15',
            // 'date' => 'required|date',
            // 'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
