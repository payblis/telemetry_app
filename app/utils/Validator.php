<?php
/**
 * Classe utilitaire pour la validation des données
 * 
 * Fournit des méthodes pour valider différents types de données
 */
namespace App\Utils;

class Validator {
    /**
     * Erreurs de validation
     */
    protected static $errors = [];
    
    /**
     * Données à valider
     */
    protected static $data = [];
    
    /**
     * Règles de validation
     */
    protected static $rules = [];
    
    /**
     * Messages d'erreur personnalisés
     */
    protected static $messages = [];
    
    /**
     * Valider des données selon des règles
     * 
     * @param array $data Données à valider
     * @param array $rules Règles de validation
     * @param array $messages Messages d'erreur personnalisés
     * @return bool Les données sont valides ou non
     */
    public static function validate($data, $rules, $messages = []) {
        // Réinitialiser les erreurs
        self::$errors = [];
        
        // Stocker les données, règles et messages
        self::$data = $data;
        self::$rules = $rules;
        self::$messages = $messages;
        
        // Parcourir les règles
        foreach ($rules as $field => $fieldRules) {
            // Vérifier si le champ existe dans les données
            $value = isset($data[$field]) ? $data[$field] : null;
            
            // Diviser les règles multiples
            $rulesArray = explode('|', $fieldRules);
            
            // Appliquer chaque règle
            foreach ($rulesArray as $rule) {
                // Vérifier si la règle a des paramètres
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $ruleParam) = explode(':', $rule, 2);
                } else {
                    $ruleName = $rule;
                    $ruleParam = null;
                }
                
                // Nom de la méthode de validation
                $method = 'validate' . ucfirst($ruleName);
                
                // Vérifier si la méthode existe
                if (method_exists(self::class, $method)) {
                    // Appeler la méthode de validation
                    $valid = self::$method($field, $value, $ruleParam);
                    
                    // Si la validation échoue, arrêter la validation pour ce champ
                    if (!$valid) {
                        break;
                    }
                }
            }
        }
        
        // Retourner true si aucune erreur
        return empty(self::$errors);
    }
    
    /**
     * Obtenir les erreurs de validation
     * 
     * @return array Erreurs de validation
     */
    public static function getErrors() {
        return self::$errors;
    }
    
    /**
     * Obtenir les erreurs pour un champ spécifique
     * 
     * @param string $field Nom du champ
     * @return array Erreurs pour le champ
     */
    public static function getFieldErrors($field) {
        return self::$errors[$field] ?? [];
    }
    
    /**
     * Ajouter une erreur de validation
     * 
     * @param string $field Nom du champ
     * @param string $rule Nom de la règle
     * @param string $message Message d'erreur
     */
    protected static function addError($field, $rule, $message) {
        // Vérifier si un message personnalisé existe
        if (isset(self::$messages[$field . '.' . $rule])) {
            $message = self::$messages[$field . '.' . $rule];
        }
        
        // Ajouter l'erreur
        if (!isset(self::$errors[$field])) {
            self::$errors[$field] = [];
        }
        
        self::$errors[$field][] = $message;
    }
    
    /**
     * Valider que le champ est requis
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateRequired($field, $value, $param) {
        $valid = $value !== null && $value !== '';
        
        if (!$valid) {
            self::addError($field, 'required', "Le champ {$field} est obligatoire.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est une adresse email
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateEmail($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        
        if (!$valid) {
            self::addError($field, 'email', "Le champ {$field} doit être une adresse email valide.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ a une longueur minimale
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Longueur minimale
     * @return bool La validation est réussie ou non
     */
    protected static function validateMin($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = strlen($value) >= $param;
        
        if (!$valid) {
            self::addError($field, 'min', "Le champ {$field} doit contenir au moins {$param} caractères.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ a une longueur maximale
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Longueur maximale
     * @return bool La validation est réussie ou non
     */
    protected static function validateMax($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = strlen($value) <= $param;
        
        if (!$valid) {
            self::addError($field, 'max', "Le champ {$field} ne doit pas dépasser {$param} caractères.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est numérique
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateNumeric($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = is_numeric($value);
        
        if (!$valid) {
            self::addError($field, 'numeric', "Le champ {$field} doit être un nombre.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est une date valide
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateDate($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = strtotime($value) !== false;
        
        if (!$valid) {
            self::addError($field, 'date', "Le champ {$field} doit être une date valide.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ correspond à un autre champ
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Nom du champ à comparer
     * @return bool La validation est réussie ou non
     */
    protected static function validateMatches($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = isset(self::$data[$param]) && $value === self::$data[$param];
        
        if (!$valid) {
            self::addError($field, 'matches', "Le champ {$field} doit correspondre au champ {$param}.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est unique dans la base de données
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Table et colonne (table.column)
     * @return bool La validation est réussie ou non
     */
    protected static function validateUnique($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        // Diviser le paramètre en table et colonne
        list($table, $column) = explode('.', $param);
        
        // Vérifier si une exception existe (pour les mises à jour)
        $exceptId = null;
        $exceptColumn = 'id';
        
        if (isset(self::$data['id'])) {
            $exceptId = self::$data['id'];
        }
        
        // Construire la requête
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $params = ['value' => $value];
        
        // Ajouter l'exception si nécessaire
        if ($exceptId !== null) {
            $sql .= " AND {$exceptColumn} != :except_id";
            $params['except_id'] = $exceptId;
        }
        
        // Exécuter la requête
        $count = Database::fetchColumn($sql, $params);
        
        $valid = $count == 0;
        
        if (!$valid) {
            self::addError($field, 'unique', "La valeur du champ {$field} est déjà utilisée.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est dans une liste de valeurs
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Liste de valeurs séparées par des virgules
     * @return bool La validation est réussie ou non
     */
    protected static function validateIn($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $allowedValues = explode(',', $param);
        $valid = in_array($value, $allowedValues);
        
        if (!$valid) {
            self::addError($field, 'in', "Le champ {$field} doit être l'une des valeurs suivantes : {$param}.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est un entier
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateInteger($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = filter_var($value, FILTER_VALIDATE_INT) !== false;
        
        if (!$valid) {
            self::addError($field, 'integer', "Le champ {$field} doit être un nombre entier.");
        }
        
        return $valid;
    }
    
    /**
     * Valider que le champ est un nombre décimal
     * 
     * @param string $field Nom du champ
     * @param mixed $value Valeur du champ
     * @param mixed $param Paramètre de la règle (non utilisé)
     * @return bool La validation est réussie ou non
     */
    protected static function validateDecimal($field, $value, $param) {
        // Si le champ est vide et n'est pas requis, la validation est réussie
        if ($value === null || $value === '') {
            return true;
        }
        
        $valid = filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        
        if (!$valid) {
            self::addError($field, 'decimal', "Le champ {$field} doit être un nombre décimal.");
        }
        
        return $valid;
    }
}
