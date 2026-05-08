<?php

namespace MiEmpresa\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSecurityQuestionsSetUp
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->security_questions_setup) {
            if (!in_array($request->route()->getName(), [
                'security-questions.setup',
                'security-questions.store',
                'logout',
            ])) {
                return redirect()->route('security-questions.setup');
            }
        }

        return $next($request);
    }
}
