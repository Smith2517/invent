<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Location;

class LocationController extends Controller
{
    private Location $locationModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->locationModel = new Location();
    }

    /**
     * Vista principal del CRUD de Locales / Sedes
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');

        $locations = $this->locationModel->all();

        $this->render('locations/index', [
            'title' => 'Locales / Sedes',
            'locations' => $locations
        ]);
    }

    /**
     * API AJAX: Detalle de un local / sede
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];

        $location = $this->locationModel->find($id);

        if (!$location) {
            $this->response->error("El local / sede no existe.", 404);
            return;
        }

        $this->response->success("Detalle obtenido correctamente.", $location);
    }

    /**
     * API AJAX: Guardar nuevo local / sede
     */
    public function ajaxSave()
    {
        $this->authorize('ROLE_EDIT');

        $code = trim($this->request->input('code', ''));
        $name = trim($this->request->input('name', ''));
        $address = trim($this->request->input('address', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($name)) {
            $this->response->error("Complete todos los campos obligatorios (Código y Nombre).");
            return;
        }

        // Validar código de local único en registros activos
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `locations` WHERE `code` = :code AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del local ya se encuentra registrado.");
            return;
        }

        $locationId = $this->locationModel->create([
            'code' => $code,
            'name' => $name,
            'address' => !empty($address) ? $address : null,
            'status' => $status
        ]);

        if ($locationId > 0) {
            $this->response->success("Local / Sede registrado correctamente.");
        } else {
            $this->response->error("No se pudo registrar el local.");
        }
    }

    /**
     * API AJAX: Actualizar local / sede existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $location = $this->locationModel->find($id);
        if (!$location) {
            $this->response->error("El local / sede no existe.", 404);
            return;
        }

        $code = trim($this->request->input('code', ''));
        $name = trim($this->request->input('name', ''));
        $address = trim($this->request->input('address', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($name)) {
            $this->response->error("Complete todos los campos obligatorios (Código y Nombre).");
            return;
        }

        // Validar código de local único excluyendo al actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `locations` WHERE `code` = :code AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del local ya está registrado por otro local.");
            return;
        }

        $updated = $this->locationModel->update($id, [
            'code' => $code,
            'name' => $name,
            'address' => !empty($address) ? $address : null,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Local / Sede actualizado correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar local / sede (Baja lógica con validación de dependencias)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $location = $this->locationModel->find($id);
        if (!$location) {
            $this->response->error("El local / sede no existe.", 404);
            return;
        }

        // Validar si tiene bienes patrimoniales asignados activos
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `location_id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar el local / sede porque tiene bienes patrimoniales asociados activos.");
            return;
        }

        if ($this->locationModel->delete($id)) {
            $this->response->success("Local / Sede eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el local.");
        }
    }
}
