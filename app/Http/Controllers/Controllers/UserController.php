<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('user.index', ['users' => $users]);
    }

    public function login()
    {
        if (View::exists('user.login')) {
            return view('user.login');
        } else {
            // return response()->view('errors.404');
            return abort(404);
        }
    }

    public function process(Request $request, RateLimiter $limiter)
    {
        $validated = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // Allow 3 login attempts within 1 minute
        if ($limiter->tooManyAttempts($this->throttleKey($request), 3, 1)) {
            // Disable sign-in button and display countdown message
            return redirect('/login')->with('error', 'Too many login attempts. Please try again after 1 minute.');
        }

        if (auth()->attempt($validated)) {
            // Successful login
            $request->session()->regenerate();
            $limiter->clear($this->throttleKey($request)); // Clear login attempts on successful login
            Session::forget('error'); // Clear login error message
            return redirect('/')->with('success', 'Welcome back!');
        } else {
            throw ValidationException::withMessages(['throttle' => trans('auth.failed')]);
        }
    }

    protected function throttleKey(Request $request)
    {
        return mb_strtolower($request->input('username')) . '|' . $request->ip();
    }


    public function register()
    {
        return view('user.register');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Logout successful');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'position' => 'required',
            'username' => ['required', Rule::unique('users', 'username')],
            'password' => 'required|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        auth()->login($user);
        return redirect(RouteServiceProvider::HOME);
    }

    public function create()
    {
        return view('user.create');
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'position' => 'required',
            'username' => ['required', Rule::unique('users', 'username')],
            'password' => 'required|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        User::create($validated);

        return redirect('/user')->with('success', 'New user was added successfully!');
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('user.edit', ['user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $data = User::find($id);

        $validated = $request->validate([
            'name' => 'required',
            'position' => 'required',
            'username' => [
                'required',
                Rule::unique('users', 'username')->ignore($data->id),
            ],
            'password' => 'nullable|min:6',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        $data->update($validated);

        return redirect('/user')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }
}
