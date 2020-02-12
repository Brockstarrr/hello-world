<?php
namespace App\Http\Middleware;
use Closure;
use Session;
class IsUserLogin
{
    public function handle($request, Closure $next)
    {
    	if (!$request->session()->has('UserLogin'))
    	{
     		return redirect('login');
		}
        
        return $next($request);
    }
}