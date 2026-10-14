<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUsuarioRequest;
use App\Http\Requests\Admin\UpdateUsuarioRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Muestra la lista de usuarios del sistema.
     */
    public function index(): View
    {
        $usuarios = User::query()
            ->withCount('bitacoras')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create(): View
    {
        $roles = User::ROLES;

        return view('admin.usuarios.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['password'] = Hash::make($datos['password']);

        User::create($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $usuario): View
    {
        $roles = User::ROLES;

        return view('admin.usuarios.edit', compact('usuario', 'roles'));
    }

    /**
     * Actualiza la información de un usuario.
     */
    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();

        if (! empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina un usuario del sistema si no es el usuario en sesión ni el último administrador.
     */
    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()?->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta en sesión.');
        }

        if ($usuario->isAdmin() && User::where('rol', 'administrador')->count() <= 1) {
            return back()->with('error', 'No es posible eliminar al único administrador del sistema.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado con éxito.');
    }
}
