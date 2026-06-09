<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Office;

class OfficeController extends Controller
{
    private Office $officeModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->officeModel = new Office();
    }

    /**
     * Vista principal del CRUD de Oficinas
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');

        $offices = $this->officeModel->all();

        $this->render('offices/index', [
            'title' => 'Oficinas / Áreas',
            'offices' => $offices
        ]);
    }

    /**
     * API AJAX: Detalle de una oficina
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];

        $office = $this->officeModel->find($id);

        if (!$office) {
            $this->response->error("La oficina no existe.", 404);
            return;
        }

        $this->response->success("Detalle de oficina obtenido", $office);
    }

    /**
     * API AJAX: Guardar nueva oficina
     */
    public function ajaxSave()
    {
        $this->authorize('ROLE_EDIT');

        $code = trim($this->request->input('code', ''));
        $name = trim($this->request->input('name', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($name)) {
            $this->response->error("Complete todos los campos obligatorios (Código y Nombre).");
            return;
        }

        // Validar unicidad del código de oficina
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `offices` WHERE `code` = :code AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código de la oficina ya se encuentra registrado.");
            return;
        }

        $officeId = $this->officeModel->create([
            'code' => $code,
            'name' => $name,
            'status' => $status
        ]);

        if ($officeId > 0) {
            $this->response->success("Oficina / Área registrada correctamente.");
        } else {
            $this->response->error("No se pudo registrar la oficina.");
        }
    }

    /**
     * API AJAX: Actualizar oficina existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $office = $this->officeModel->find($id);
        if (!$office) {
            $this->response->error("La oficina no existe.", 404);
            return;
        }

        $code = trim($this->request->input('code', ''));
        $name = trim($this->request->input('name', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($name)) {
            $this->response->error("Complete todos los campos obligatorios (Código y Nombre).");
            return;
        }

        // Validar unicidad del código de oficina excluyendo al actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `offices` WHERE `code` = :code AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código de la oficina ya está registrado en otra oficina.");
            return;
        }

        $updated = $this->officeModel->update($id, [
            'code' => $code,
            'name' => $name,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Oficina / Área actualizada correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar oficina (Baja lógica con validaciones de dependencias)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $office = $this->officeModel->find($id);
        if (!$office) {
            $this->response->error("La oficina no existe.", 404);
            return;
        }

        $db = \App\Core\Database::getConnection();

        // 1. Validar que no tenga bienes patrimoniales asignados activos
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `office_id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar la oficina porque tiene bienes patrimoniales asociados activos.");
            return;
        }

        // 2. Validar que no tenga responsables de custodio activos asociados
        $stmt = $db->prepare("SELECT COUNT(*) FROM `responsibles` WHERE `office_id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar la oficina porque tiene responsables de custodio asociados activos.");
            return;
        }

        if ($this->officeModel->delete($id)) {
            $this->response->success("Oficina eliminada correctamente.");
        } else {
            $this->response->error("No se pudo eliminar la oficina.");
        }
    }
}
