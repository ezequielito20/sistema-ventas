<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImageProxyController extends Controller
{
    /**
     * Servir imagen a través de proxy
     */
    public function serve(Request $request, string $path)
    {
        try {
            // Decodificar la ruta
            $imagePath = base64_decode($path);
            
            // Validar que la ruta es segura
            if (!$this->isValidImagePath($imagePath)) {
                return response()->json(['error' => 'Invalid image path'], 400);
            }
            
            $disk = Storage::disk('private');
            
            // Verificar que el archivo existe
            if (!$disk->exists($imagePath)) {
                return response()->json(['error' => 'Image not found'], 404);
            }
            
            // Obtener el contenido del archivo
            $content = $disk->get($imagePath);
            
            // Obtener el tipo MIME
            $mimeType = $this->getMimeType($imagePath);
            
            // Crear respuesta con headers de cache
            return Response::make($content, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000', // 1 año
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
                'Last-Modified' => gmdate('D, d M Y H:i:s', $disk->lastModified($imagePath)) . ' GMT',
                'ETag' => md5($content),
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error sirviendo imagen: {$e->getMessage()}", [
                'path' => $path,
                'decoded_path' => $imagePath ?? 'unknown'
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    
    /**
     * Validar que la ruta de imagen es segura
     */
    private function isValidImagePath(string $path): bool
    {
        // Prevenir directory traversal
        if (str_contains($path, '..') || str_contains($path, '//')) {
            return false;
        }
        
        // Solo permitir rutas que empiecen con 'products/'
        if (!str_starts_with($path, 'products/')) {
            return false;
        }
        
        // Verificar extensión de archivo
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        return in_array($extension, $allowedExtensions);
    }
    
    /**
     * Obtener tipo MIME basado en la extensión
     */
    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
