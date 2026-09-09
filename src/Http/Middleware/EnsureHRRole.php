<?php

namespace Khurrambaig\HRManagement\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Khurrambaig\HRManagement\Models\Employee;
use Symfony\Component\HttpFoundation\Response;

class EnsureHRRole
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User $user */
        $user = $request->user();

        $employee = Employee::where('user_id', $user->getAuthIdentifier())->first();

        if (! $employee) {
            return redirect()->route('hr.no-profile');
        }

        $request->attributes->set('hr.employee', $employee);

        View::share('hrEmployee', $employee);

        return $next($request);
    }
}