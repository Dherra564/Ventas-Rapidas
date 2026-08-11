<?php

trait ManejadorImagenes
{
    
    protected function subirImagenPerfil(?array $archivo, string $prefijo): string|false
    {
        if ($archivo === null || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return false;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("Error al subir la imagen (código {$archivo['error']})");
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $tamanoMaximoBytes = 5 * 1024 * 1024; // 5 MB

        if ($archivo['size'] > $tamanoMaximoBytes) {
            throw new InvalidArgumentException("La imagen no puede pesar más de 5 MB");
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas, true)) {
            throw new InvalidArgumentException("Formato de imagen no permitido. Usa: " . implode(', ', $extensionesPermitidas));
        }

        $carpetaDestino = __DIR__ . '/../../Publico/imagenes/';

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        $nombreArchivo = $prefijo . '_' . uniqid() . '.' . $extension;
        $rutaDestino = $carpetaDestino . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new InvalidArgumentException("No se pudo guardar la imagen");
        }

        return $nombreArchivo;
    }
}