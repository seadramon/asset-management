<?php

namespace Asset\Http\Middleware;

use Closure;

class VerifyNip
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($nip = $request->header('nip')) {
            $bagian = bagian($nip);
            $lokasi = lokasi($nip);

            if (count($bagian) < 0 || count($lokasi) < 0) {
                return response()->json([
                    'errors' => 'Not Found',
                    'message' => 'Data Bagian atau Lokasi dari NIP anda tidak ditemukan. Hubungi Administrator '], 404);        
            }

            return $next($request);
        }

        return response()->json([
            'errors' => 'Unauthorized',
            'message' => 'Empty NIP'], 401);
    }
}
