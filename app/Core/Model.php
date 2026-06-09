<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $useSoftDelete = true;
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtener todos los registros activos (sin deleted_at si usa soft delete)
     */
    public function all(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($this->useSoftDelete) {
            $sql .= " WHERE `deleted_at` IS NULL";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Encontrar un registro por su clave primaria
     */
    public function find($id)
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        if ($this->useSoftDelete) {
            $sql .= " AND `deleted_at` IS NULL";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo registro
     */
    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = implode(', ', array_map(fn($f) => ":{$f}", $fields));
        $columns = implode(', ', array_map(fn($f) => "`{$f}`", $fields));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        $insertId = (int)$this->db->lastInsertId();
        
        // Registrar en la bitácora
        $this->logActivity('CREATE', $insertId, null, $data);

        return $insertId;
    }

    /**
     * Actualizar un registro existente
     */
    public function update($id, array $data): bool
    {
        $oldData = $this->find($id);
        if (!$oldData) {
            return false;
        }

        $fields = array_keys($data);
        $setClause = implode(', ', array_map(fn($f) => "`{$f}` = :{$f}", $fields));

        $sql = "UPDATE `{$this->table}` SET {$setClause} WHERE `{$this->primaryKey}` = :_id_val";
        
        $stmt = $this->db->prepare($sql);
        
        // Unir los datos para la consulta
        $bindData = $data;
        $bindData['_id_val'] = $id;
        
        $result = $stmt->execute($bindData);
        
        if ($result) {
            // Registrar en la bitácora
            $this->logActivity('UPDATE', $id, $oldData, $this->find($id));
        }

        return $result;
    }

    /**
     * Eliminar un registro (físicamente o de forma lógica)
     */
    public function delete($id): bool
    {
        $oldData = $this->find($id);
        if (!$oldData) {
            return false;
        }

        if ($this->useSoftDelete) {
            $sql = "UPDATE `{$this->table}` SET `deleted_at` = NOW() WHERE `{$this->primaryKey}` = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            if ($result) {
                $this->logActivity('DELETE', $id, $oldData, ['deleted_at' => date('Y-m-d H:i:s')]);
            }
            return $result;
        } else {
            $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute(['id' => $id]);
            
            if ($result) {
                $this->logActivity('HARD_DELETE', $id, $oldData, null);
            }
            return $result;
        }
    }

    /**
     * Registrar actividad en la bitácora de auditoría
     */
    protected function logActivity(string $action, int $recordId, ?array $oldData, ?array $newData)
    {
        // Evitar bucle infinito si se registra en la tabla de bitácora
        if ($this->table === 'audit_logs') {
            return;
        }

        $userId = Session::get('user_id');
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        // Estructurar detalles de cambios
        $details = null;
        if ($oldData !== null || $newData !== null) {
            $details = json_encode([
                'old' => $oldData,
                'new' => $newData
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `module`, `affected_table`, `affected_record_id`, `ip_address`, `user_agent`, `details`) 
                    VALUES (:user_id, :action, :module, :table, :record_id, :ip, :user_agent, :details)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id'   => $userId,
                'action'    => $action,
                'module'    => ucfirst($this->table),
                'table'     => $this->table,
                'record_id' => $recordId,
                'ip'        => $ip,
                'user_agent'=> substr($userAgent, 0, 255),
                'details'   => $details
            ]);
        } catch (\PDOException $e) {
            // Ignorar fallos de escritura de logs en modo producción para no detener la ejecución principal
            if (APP_ENV === 'development') {
                die("Error al registrar auditoría: " . $e->getMessage());
            }
        }
    }
}
