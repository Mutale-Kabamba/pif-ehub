<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('admin_user_id');

        if ($userId === null) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);

        if ($user === null) {
            $request->session()->forget('admin_user_id');
            return redirect()->route('admin.login');
        }

        // Share the authenticated user with views and session
        view()->share('authUser', $user);
        session(['auth_role' => $user->name]);

        return $next($request);
    }
}
