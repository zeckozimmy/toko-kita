<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
    // Method untuk menampilkan halaman manajemen user
    public function index()
    {
        $data = User::paginate(10);
        return view('admin.page.user', [
            'name' => "User Management",
            'title' => 'Admin User Management',
            'data' => $data,
        ]);
    }

    // Method untuk menampilkan modal penambahan user
    public function addModalUser()
    {
        return view('admin.modal.modalUser', [
            'title' => 'Tambah Data User',
            'nik' => date('Ymd') . rand(000, 999),
        ]);
    }

    // Method untuk menyimpan data user baru
    public function store(UserRequest $request)
    {
        $data = new User;
        $data->nik = $request->nik;
        $data->name = $request->nama;
        $data->email = $request->email;
        $data->password = bcrypt($request->password);
        $data->alamat = $request->alamat;
        $data->tlp = $request->tlp;
        $data->role = $request->role ?? 'default_role'; // Ensure 'role' is not null
        $data->tglLahir = $request->tglLahir;
        $data->is_active = 1;
        $data->is_mamber = 0;
        $data->is_admin = 1;

        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            $filename = date('Ymd') . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('storage/user'), $filename);
            $data->foto = $filename;
        } else {
            $data->foto = 'default.png'; // Handle default photo if no file is uploaded
        }

        $data->save();
        Alert::toast('Data berhasil disimpan', 'success');
        return redirect()->route('userManagement');
    }
    // Method untuk menampilkan modal edit user
    public function show($id)
    {
        $data = User::findOrFail($id);
        return view('admin.modal.editUser', [
            'title' => 'Edit data User',
            'data' => $data,
        ])->render();
    }

    // Method untuk memperbarui data user
    public function update(UserRequest $request, $id)
    {
        $data = User::findOrFail($id);

        $filename = $data->foto; // Preserve existing filename if no new photo is uploaded
        if ($request->hasFile('foto')) {
            $photo = $request->file('foto');
            $filename = date('Ymd') . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('storage/user'), $filename);
        }

        $data->nik = $request->nik;
        $data->name = $request->nama;
        $data->email = $request->email;
        $data->password = $request->password ? bcrypt($request->password) : $data->password; // Update password only if provided
        $data->alamat = $request->alamat;
        $data->tlp = $request->tlp;
        $data->tglLahir = $request->tglLahir;
        $data->role = $request->role ?? $data->role; // Preserve existing role if not updated
        $data->foto = $filename;

        $data->save();
        Alert::toast('Data berhasil diupdate', 'success');
        return redirect()->route('userManagement');
    }

    // Method untuk menghapus user
    public function destroy($id)
    {
        $product = User::findOrFail($id);
        $product->delete();

        $json = [
            'success' => "Data berhasil dihapus"
        ];

        echo json_encode($json);
    }

    // Method untuk menyimpan data pelanggan baru
    public function storePelanggan(UserRequest $request)
    {
        $data = new User;
        $nik = "Member" . rand(000, 999);
        $data->nik = $nik;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->password = bcrypt($request->password);
        $data->alamat = $request->alamat . " " . $request->alamat2;
        $data->tlp = $request->tlp;
        $data->role = 0;
        $data->tglLahir = $request->date;
        $data->is_active = 1;
        $data->is_mamber = 1;
        $data->is_admin = 0;

        if ($request->hasFile('foto') == "") {
            $filename = "default.png";
            $data->foto = $filename;
        } else {
            $photo = $request->file('foto');
            $filename = date('Ymd') . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('storage/user'), $filename);
            $data->foto = $filename;
        }
        $data->save();
        Alert::toast('Data berhasil disimpan', 'success');
        return redirect()->route('home');
    }

    // Method untuk login pelanggan
    public function loginProses(Request $request)
    {
        $dataLogin = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = new User;
        $proses = $user::where('email', $request->email)->first();

        if ($proses === null) {
            Alert::toast('Email tidak terdaftar', 'error');
            return back();
        }

        if ($proses->is_active === 0) {
            Alert::toast('Akun kamu belum aktif', 'error');
            return back();
        }

        if (Auth::attempt($dataLogin)) {
            Alert::toast('Kamu berhasil login', 'success');
            $request->session()->regenerate();
            return redirect()->intended('/');
        } else {
            Alert::toast('Email dan Password salah', 'error');
            return back();
        }
    }

    // Method untuk logout pelanggan
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        Alert::toast('Kamu berhasil Logout', 'success');
        return redirect('/');
    }
}
