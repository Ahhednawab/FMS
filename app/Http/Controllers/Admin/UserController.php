<?php

namespace App\Http\Controllers\Admin;

use App\Models\City;
use App\Models\Role;
use App\Models\User;
use App\Models\Draft;
use App\Models\Warehouse;
use App\Traits\DraftTrait;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    use DraftTrait;
    public function index()
    {
        // $users = User::with(['designation'])->where('role','!=','admin')->where('is_active',1)->get();
        $users = User::with(['designation', 'role'])->where('designation_id', '!=', 0)->where('id', '!=', Auth::user()->id)->where('is_active', 1)->get();
        return view('admin.users.index', compact('users'));
    }

    public function create(Request $request)
    {
        $serial_no = User::GetSerialNumber();

        $roles = Role::all();
        $designation = Designation::where('is_active', 1)->orderBy('designation', 'ASC')->get();

        $draftInfo = $this->getDraftDataForView($request, 'users');

        return view('admin.users.create', compact('serial_no', 'designation', 'roles') + $draftInfo);
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
                'role_id'           => 'required',
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
                'role_id.required'          => 'Role is required',
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
        $user->role_id          =   $request->role_id;
        $user->designation_id   =   $request->designation_id;
        $user->phone            =   $request->phone;
        $user->password         =   Hash::make($request->password);
        // $user->role             =   ($request->designation_id == 3) ? 'manager' : 'user';
        $user->address          =   $request->address;
        $user->save();

        // Delete draft if it exists
        $this->deleteDraftAfterSuccess($request, 'users');

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $designation = Designation::where('is_active', 1)->orderBy('designation', 'ASC')->get();
        return view('admin.users.edit', compact('user', 'designation', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'full_name'     => 'required|string|max:255',
                'email'         => 'required|email|unique:users,email,' . $user->id,
                'role_id'           => 'required',
                'designation_id' => 'required',
                'phone'         => 'required|string|max:12|unique:users,phone,' . $user->id,
                'password'      => 'nullable|string|min:6|confirmed',
                'address'       => 'required',
            ],
            [
                'full_name.required'        => 'Full Name is required',
                'email.required'            => 'Email is required',
                'role_id.required'          => 'Role is required',
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
        $user->role_id = $request->role_id;;
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

    public function getManagers(Request $request)
    {
        try {
            $type = $request->type;

            if (!$type) {
                return response()->json([
                    'success' => false,
                    'data'    => [],
                    'message' => 'Warehouse type is required'
                ]);
            }

            // Already assigned managers
            $assignedManagerIds = Warehouse::where('type', $type)
                ->whereNotNull('manager_id')
                ->pluck('manager_id');

            // Role mapping
            $roleMap = [
                'master' => 'master-warehouse',
                'sub'    => 'sub-warehouse',
            ];

            if (!isset($roleMap[$type])) {
                return response()->json([
                    'success' => false,
                    'data'    => [],
                    'message' => 'Invalid warehouse type'
                ]);
            }

            $managers = User::whereHas('role', function ($q) use ($roleMap, $type) {
                $q->where('slug', $roleMap[$type]);
            })
                ->whereNotIn('id', $assignedManagerIds)
                ->where('is_active', 1)
                ->pluck('name', 'id');

            return response()->json([
                'success' => true,
                'data'    => $managers,
                'message' => 'Managers fetched successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'data'    => [],
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
