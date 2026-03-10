<?php
/**
 * Helpers de Archivos
 */

/**
 * Subir archivo
 */
function uploadFile($file, $directory = 'uploads') {
    try {
        // Validar que el archivo existe
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'error' => 'No se recibió ningún archivo'];
        }
        
        // Validar errores
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Error al subir el archivo'];
        }
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Ruta completa
        $uploadPath = UPLOAD_PATH . '/' . $directory;
        
        // Crear directorio si no existe
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $filepath = $uploadPath . '/' . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => asset("uploads/{$directory}/{$filename}")
            ];
        }
        
        return ['success' => false, 'error' => 'Error al guardar el archivo'];
        
    } catch (Exception $e) {
        error_log('Error en uploadFile: ' . $e->getMessage());
        return ['success' => false, 'error' => 'Error al procesar el archivo'];
    }
}

/**
 * Eliminar archivo
 */
function deleteFile($filepath) {
    try {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    } catch (Exception $e) {
        error_log('Error en deleteFile: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtener tamaño de archivo formateado
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Obtener extensión de archivo
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}