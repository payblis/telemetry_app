<?php
/**
 * Classe utilitaire pour les opérations sur les fichiers
 * 
 * Fournit des méthodes pour manipuler les fichiers et les dossiers
 */
namespace App\Utils;

class FileManager {
    /**
     * Types de fichiers autorisés par défaut
     */
    protected static $allowedTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif'],
        'video' => ['video/mp4', 'video/quicktime', 'video/x-msvideo'],
        'data' => ['application/json', 'text/csv', 'application/xml'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    ];
    
    /**
     * Taille maximale de fichier par défaut (100 MB)
     */
    protected static $maxFileSize = 104857600;
    
    /**
     * Télécharger un fichier
     * 
     * @param array $file Fichier ($_FILES['field'])
     * @param string $destination Répertoire de destination
     * @param array $options Options supplémentaires
     * @return array|bool Informations sur le fichier téléchargé ou false en cas d'échec
     */
    public static function upload($file, $destination, $options = []) {
        // Vérifier si le fichier existe
        if (!isset($file) || !is_array($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Options par défaut
        $defaultOptions = [
            'allowed_types' => [],
            'max_size' => self::$maxFileSize,
            'rename' => true,
            'create_dir' => true
        ];
        
        // Fusionner les options
        $options = array_merge($defaultOptions, $options);
        
        // Vérifier la taille du fichier
        if ($file['size'] > $options['max_size']) {
            return false;
        }
        
        // Vérifier le type de fichier
        if (!empty($options['allowed_types']) && !in_array($file['type'], $options['allowed_types'])) {
            return false;
        }
        
        // Créer le répertoire de destination si nécessaire
        if ($options['create_dir'] && !is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        // Générer un nom de fichier unique si demandé
        if ($options['rename']) {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
        } else {
            $filename = $file['name'];
        }
        
        // Chemin complet du fichier
        $filepath = rtrim($destination, '/') . '/' . $filename;
        
        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return false;
        }
        
        // Retourner les informations sur le fichier
        return [
            'name' => $filename,
            'original_name' => $file['name'],
            'type' => $file['type'],
            'size' => $file['size'],
            'path' => $filepath
        ];
    }
    
    /**
     * Supprimer un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @return bool Succès de l'opération
     */
    public static function delete($filepath) {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Créer un répertoire
     * 
     * @param string $path Chemin du répertoire
     * @param int $permissions Permissions du répertoire
     * @param bool $recursive Création récursive
     * @return bool Succès de l'opération
     */
    public static function createDirectory($path, $permissions = 0755, $recursive = true) {
        if (!is_dir($path)) {
            return mkdir($path, $permissions, $recursive);
        }
        
        return true;
    }
    
    /**
     * Vérifier si un fichier existe
     * 
     * @param string $filepath Chemin du fichier
     * @return bool Le fichier existe ou non
     */
    public static function exists($filepath) {
        return file_exists($filepath);
    }
    
    /**
     * Obtenir l'extension d'un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @return string Extension du fichier
     */
    public static function getExtension($filepath) {
        return pathinfo($filepath, PATHINFO_EXTENSION);
    }
    
    /**
     * Obtenir le nom d'un fichier sans l'extension
     * 
     * @param string $filepath Chemin du fichier
     * @return string Nom du fichier sans extension
     */
    public static function getFilename($filepath) {
        return pathinfo($filepath, PATHINFO_FILENAME);
    }
    
    /**
     * Obtenir la taille d'un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @return int Taille du fichier en octets
     */
    public static function getSize($filepath) {
        if (file_exists($filepath)) {
            return filesize($filepath);
        }
        
        return 0;
    }
    
    /**
     * Obtenir le type MIME d'un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @return string Type MIME du fichier
     */
    public static function getMimeType($filepath) {
        if (file_exists($filepath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filepath);
            finfo_close($finfo);
            return $mime;
        }
        
        return '';
    }
    
    /**
     * Lire le contenu d'un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @return string|bool Contenu du fichier ou false en cas d'échec
     */
    public static function read($filepath) {
        if (file_exists($filepath)) {
            return file_get_contents($filepath);
        }
        
        return false;
    }
    
    /**
     * Écrire dans un fichier
     * 
     * @param string $filepath Chemin du fichier
     * @param string $content Contenu à écrire
     * @param bool $append Ajouter au contenu existant
     * @return bool Succès de l'opération
     */
    public static function write($filepath, $content, $append = false) {
        $flags = 0;
        
        if ($append) {
            $flags = FILE_APPEND;
        }
        
        // Créer le répertoire si nécessaire
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        return file_put_contents($filepath, $content, $flags) !== false;
    }
    
    /**
     * Copier un fichier
     * 
     * @param string $source Chemin du fichier source
     * @param string $destination Chemin du fichier de destination
     * @return bool Succès de l'opération
     */
    public static function copy($source, $destination) {
        if (file_exists($source)) {
            // Créer le répertoire de destination si nécessaire
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            return copy($source, $destination);
        }
        
        return false;
    }
    
    /**
     * Déplacer un fichier
     * 
     * @param string $source Chemin du fichier source
     * @param string $destination Chemin du fichier de destination
     * @return bool Succès de l'opération
     */
    public static function move($source, $destination) {
        if (file_exists($source)) {
            // Créer le répertoire de destination si nécessaire
            $directory = dirname($destination);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            return rename($source, $destination);
        }
        
        return false;
    }
    
    /**
     * Obtenir la liste des fichiers d'un répertoire
     * 
     * @param string $directory Chemin du répertoire
     * @param string $pattern Motif de recherche
     * @return array Liste des fichiers
     */
    public static function getFiles($directory, $pattern = '*') {
        $files = [];
        
        if (is_dir($directory)) {
            $glob = glob(rtrim($directory, '/') . '/' . $pattern);
            
            foreach ($glob as $file) {
                if (is_file($file)) {
                    $files[] = $file;
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Obtenir la liste des répertoires d'un répertoire
     * 
     * @param string $directory Chemin du répertoire
     * @return array Liste des répertoires
     */
    public static function getDirectories($directory) {
        $directories = [];
        
        if (is_dir($directory)) {
            $items = scandir($directory);
            
            foreach ($items as $item) {
                if ($item != '.' && $item != '..' && is_dir($directory . '/' . $item)) {
                    $directories[] = $directory . '/' . $item;
                }
            }
        }
        
        return $directories;
    }
    
    /**
     * Vérifier si un type de fichier est autorisé
     * 
     * @param string $mimeType Type MIME du fichier
     * @param string $category Catégorie de fichier (image, video, data, document)
     * @return bool Le type est autorisé ou non
     */
    public static function isAllowedType($mimeType, $category = null) {
        if ($category !== null) {
            return isset(self::$allowedTypes[$category]) && in_array($mimeType, self::$allowedTypes[$category]);
        }
        
        foreach (self::$allowedTypes as $types) {
            if (in_array($mimeType, $types)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Définir les types de fichiers autorisés
     * 
     * @param array $types Types de fichiers par catégorie
     */
    public static function setAllowedTypes($types) {
        self::$allowedTypes = $types;
    }
    
    /**
     * Définir la taille maximale de fichier
     * 
     * @param int $size Taille maximale en octets
     */
    public static function setMaxFileSize($size) {
        self::$maxFileSize = $size;
    }
}
