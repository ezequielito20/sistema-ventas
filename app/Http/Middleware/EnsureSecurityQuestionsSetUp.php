<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSecurityQuestionsSetUp
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // El super admin está completamente exento de las preguntas de seguridad
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

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
