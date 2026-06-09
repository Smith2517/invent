<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Obtener la instancia única de conexión PDO (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // En producción, no mostrar detalles de la base de datos por seguridad
                if (APP_ENV === 'development') {
                    die("Error de conexión a la Base de Datos: " . $e->getMessage());
                } else {
                    die("Ha ocurrido un error inesperado de conexión. Por favor intente más tarde.");
                }
            }
        }

        return self::$instance;
    }
}
