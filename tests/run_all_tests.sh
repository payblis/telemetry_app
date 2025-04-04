#!/bin/bash

# Script pour exécuter tous les tests de l'application

echo "=== EXÉCUTION DE TOUS LES TESTS DE L'APPLICATION ==="
echo ""

# Exécuter le test général de l'application
echo "1. Test général de l'application"
php application_test.php
echo ""

# Exécuter le test d'intégration de Sensor Logger
echo "2. Test d'intégration de Sensor Logger"
php sensor_logger_test.php
echo ""

# Exécuter le test d'intégration de l'IA
echo "3. Test d'intégration de l'IA"
php ai_integration_test.php
echo ""

echo "=== TOUS LES TESTS SONT TERMINÉS ==="
