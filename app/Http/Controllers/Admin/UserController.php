<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\City;
use App\Models\Designation;
use App\Models\Draft;
use App\Traits\DraftTrait;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    use DraftTrait;
    public function index()
    {
        // $users = User::with(['designation'])->where('role','!=','admin')->where('is_active',1)->get();
        $users = User::with(['designation'])->where('designation_id','!=',0)->where('id','!=',Auth::user()->id)->where('is_active',1)->get();
        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $serial_no = User::GetSerialNumber();
        $designation = Designation::where('is_active',1)->orderBy('designation','ASC')->get();
        
        $draftData = $this->getDraftDataForView($request, 'users');
        
        return view('admin.users.create', compact('serial_no','designation') + $draftData);
    }

    public function store(Request $request)
    {
        // Handle draft saving
        if ($this->handleDraftSave($request, 'users')) {
            return redirect()->back()->with('success', 'Draft saved successfully!');
        }

        $validator = \Validator::make(
            $request->all(),
            [
                'full_name'         => 'required',
                'email'             => 'required|email|unique:users,email',
                'designation_id'    => 'required',
                'phone'             => 'required|string|max:12|unique:users,phone',
                'password'          => 'required|string|min:6|confirmed',
                // 'country_id'        => 'required',
                // 'city_id'           => 'required',
                'address'           => 'required',
            ],
            [
                'full_name.required'        => 'Full Name is required',
                'email.required'            => 'Email is required',
                'designation_id.required'   => 'Designation is required',
                'phone.required'            => 'Phone is required',
                'password.required'         => 'Password is required',
                'password.confirmed'        => 'Password confirmation does not match',
                // 'country_id.required'       => 'Country is required.',
                // 'city_id.required'          => 'City is required.',
                'address.required'          => 'Address is required.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->serial_no        =   $request->serial_no;
        $user->name             =   $request->full_name;
        $user->email            =   $request->email;
        $user->designation_id   =   $request->designation_id;
        $user->phone            =   $request->phone;
        $user->password         =   Hash::make($request->password);
        // $user->role             =   ($request->designation_id == 3) ? 'manager' : 'user';
        $user->role = 'admin';
        $user->address          =   $request->address;        
        $user->save();

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'users');

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $designation = Designation::where('is_active',1)->orderBy('designation','ASC')->get();
        return view('admin.users.edit', compact('user','designation'));
    }

    public function update(Request $request, User $user)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'full_name'     => 'required|string|max:255',
                'email'         => 'required|email|unique:users,email,' . $user->id,
                'designation_id'=> 'required',
                'phone'         => 'required|string|max:12|unique:users,phone,' . $user->id,
                'password'      => 'nullable|string|min:6|confirmed',
                'address'       => 'required',
            ],
            [
                'full_name.required'        => 'Full Name is required',
                'email.required'            => 'Email is required',
                'designation_id.required'   => 'Designation is required',
                'phone.required'            => 'Phone is required',
                'password.confirmed'        => 'Password confirmation does not match',
                'address.required'          => 'Address is required.',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user->name             =   $request->full_name;
        $user->email            =   $request->email;
        $user->designation_id   =   $request->designation_id;
        $user->phone            =   $request->phone;
        $user->password         =   Hash::make($request->password);
        $user->country_id       =   $request->country_id;
        $user->city_id          =   $request->city_id;
        // $user->role             =   ($request->designation_id == 3) ? 'manager' : 'user';
        $user->role = 'admin';
        $user->address          =   $request->address;       
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->is_active = 0;
        $user->save();

        return redirect()->route('admin.users.index')->with('delete_msg', 'User deleted successfully.');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }
}
