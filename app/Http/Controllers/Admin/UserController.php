<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('email')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        User::create([
            'name' => str($validated['email'])->before('@')->toString(),
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
            'is_admin' => (bool) ($validated['is_admin'] ?? false),
        ]);

        $createdUser = User::query()->where('email', $validated['email'])->firstOrFail();
        AuditLogger::logCreate(User::class, $createdUser->id, [
            'email' => $createdUser->email,
            'is_admin' => $createdUser->isAdmin(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $oldValues = [
            'email' => $user->email,
            'is_admin' => $user->isAdmin(),
        ];
        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_admin' => ['sometimes', 'boolean'],
        ]);

        $willBeAdmin = (bool) ($validated['is_admin'] ?? false);
        if ($user->isAdmin() && ! $willBeAdmin && User::query()->where('is_admin', true)->count() === 1) {
            return back()->withErrors(['is_admin' => 'Debe existir al menos un administrador.'])->withInput();
        }

        $user->fill([
            'name' => str($validated['email'])->before('@')->toString(),
            'email' => $validated['email'],
            'is_admin' => $willBeAdmin,
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        AuditLogger::logUpdate(User::class, $user->id, $oldValues, [
            'email' => $user->email,
            'is_admin' => $user->isAdmin(),
            'password_changed' => ! empty($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'No puede eliminar su propia cuenta mientras está conectado.']);
        }

        if ($user->isAdmin() && User::query()->where('is_admin', true)->count() === 1) {
            return back()->withErrors(['user' => 'No puede eliminar el único administrador.']);
        }

        $oldValues = ['email' => $user->email, 'is_admin' => $user->isAdmin()];
        $userId = $user->id;
        $user->delete();
        AuditLogger::logDelete(User::class, $userId, $oldValues);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
