<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * The roles that are allowed to access the route.
     *
     * @var array<string>
     */
    private array $allowedRoles;

    /**
     * Create a new middleware instance.
     *
     * @param array<string> $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

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

        if (! in_array($user->role, $this->allowedRoles, true)) {
            abort(403, 'Unauthorized role.');
        }

        return $next($request);
    }
}
