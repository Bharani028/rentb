<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $q = $request->string('q')->toString();
            $role = $request->string('role')->toString();
            $status = $request->string('status')->toString();

            $query = User::query()
                ->with('roles')
                ->when($q, function (Builder $query) use ($q) {
                    $query->where(function (Builder $w) use ($q) {
                        $w->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%");
                    });
                })
                ->when($role, function (Builder $qB) use ($role) {
                    if ($role === 'guest') {
                        $qB->doesntHave('roles');
                    } else {
                        $qB->whereHas('roles', fn ($q) => $q->where('name', $role));
                    }
                })
                ->when($status, fn (Builder $qB) => $qB->where('status', $status));

            // Handle sorting from DataTable
            $orderColumn = $request->get('order')[0]['column'] ?? 0;
            $orderDirection = $request->get('order')[0]['dir'] ?? 'asc';

            $columns = ['name', 'email', 'roles.name', 'created_at', 'status', 'actions'];
            $orderBy = $columns[$orderColumn] ?? 'created_at';

            if ($orderBy === 'roles.name') {
                $query->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                      ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                      ->orderBy('roles.name', $orderDirection);
            } else {
                $query->orderBy($orderBy, $orderDirection);
            }

            return DataTables::of($query)
                ->addColumn('user_link', function ($user) {
                    $name = $user->name ?? 'User #' . $user->id;
                    return '<a href="#" class="text-decoration-none view-user" data-id="' . $user->id . '">' . $name . '</a>';
                })
                ->addColumn('email', function ($user) {
                    return $user->email;
                })
                ->addColumn('role_badge', function ($user) {
                    $role = $user->roles->isNotEmpty() ? $user->roles->first()->name : 'guest';
                    return '<span class="badge role-badge">' . ucfirst($role) . '</span>';
                })
                ->addColumn('joined', function ($user) {
                    return $user->created_at ? $user->created_at->format('d M Y') : '—';
                })
                ->addColumn('status_badge', function ($user) {
                    return '<span class="badge status-' . $user->status . '">' . ucfirst($user->status) . '</span>';
                })
                ->addColumn('actions', function ($user) {
                    $buttons = '<div class="btn-group">';
                    $buttons .= '<button class="btn btn-sm btn-outline-secondary view-user" data-id="' . $user->id . '" title="View"><i class="bi bi-eye"></i></button>';
                    if ($user->status === 'inactive') {
                        $buttons .= '<form action="' . route('admin.users.restore', $user->id) . '" method="POST" class="d-inline">' .
                                    '<input type="hidden" name="_token" value="' . csrf_token() . '">' .
                                    '<button class="btn btn-sm btn-success js-restore" title="Restore" data-title="' . ($user->name ?? 'User #' . $user->id) . '"><i class="bi bi-play-circle"></i></button>' .
                                    '</form>';
                    } else {
                        $buttons .= '<form action="' . route('admin.users.suspend', $user->id) . '" method="POST" class="d-inline">' .
                                    '<input type="hidden" name="_token" value="' . csrf_token() . '">' .
                                    '<button class="btn btn-sm btn-outline-danger js-suspend" title="Suspend" data-title="' . ($user->name ?? 'User #' . $user->id) . '"><i class="bi bi-pause-circle"></i></button>' .
                                    '</form>';
                    }
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['user_link', 'role_badge', 'status_badge', 'actions'])
                ->make(true);
        }

        // Roles list for filter dropdown
        $roles = ['admin', 'users', 'guest'];

        return view('admin.users.index', compact('roles'));
    }

    public function show(User $user)
    {
        if (request()->ajax()) {
            return response()->json([
                'name' => $user->name ?? 'User #' . $user->id,
                'email' => $user->email,
                'roles' => $user->roles->map(function ($role) {
                    return ['name' => $role->name];
                })->all(),
                'created_at' => $user->created_at,
                'status' => $user->status,
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $roles = ['admin', 'users', 'guest'];
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['nullable', 'string', 'max:50'],
        ]);

        $user = new User();
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->status = 'active';
        $user->save();

        if (!empty($data['role']) && $data['role'] !== 'guest') {
            $user->assignRole($data['role']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $roles = ['admin', 'users', 'guest'];
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role'  => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        if (!empty($data['role'])) {
            if ($data['role'] === 'guest') {
                $user->syncRoles([]);
            } else {
                $user->syncRoles($data['role']);
            }
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function suspend(User $user)
    {
        $user->status = 'inactive';
        $user->save();

        return back()->with('success', 'User suspended.');
    }

    public function restore(User $user)
    {
        $user->status = 'active';
        $user->save();

        return back()->with('success', 'User restored.');
    }
}