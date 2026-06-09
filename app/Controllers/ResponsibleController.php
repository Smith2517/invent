<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Responsible;
use App\Models\Office;

class ResponsibleController extends Controller
{
    private Responsible $responsibleModel;
    private Office $officeModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->responsibleModel = new Responsible();
        $this->officeModel = new Office();
    }

    /**
     * Vista principal del CRUD de Responsables
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');

        $responsibles = $this->responsibleModel->allWithOffice();
        $offices = $this->officeModel->all();

        $this->render('responsibles/index', [
            'title' => 'Responsables de Custodio',
            'responsibles' => $responsibles,
            'offices' => $offices
        ]);
    }

    /**
     * API AJAX: Detalle de un responsable
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];

        $responsible = $this->responsibleModel->find($id);

        if (!$responsible) {
            $this->response->error("El responsable no existe.", 404);
            return;
        }

        $this->response->success("Detalle de responsable obtenido", $responsible);
    }

    /**
     * API AJAX: Guardar nuevo responsable
     */
    public function ajaxSave()
    {
        $this->authorize('ROLE_EDIT');

        $dni = trim($this->request->input('dni', ''));
        $names = trim($this->request->input('names', ''));
        $surnames = trim($this->request->input('surnames', ''));
        $position = trim($this->request->input('position', ''));
        $office_id = (int)$this->request->input('office_id', 0);
        $email = trim($this->request->input('email', ''));
        $phone = trim($this->request->input('phone', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($dni) || empty($names) || empty($surnames) || empty($office_id)) {
            $this->response->error("Complete todos los campos obligatorios (DNI, Nombres, Apellidos y Oficina).");
            return;
        }

        // Validar unicidad del DNI
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `responsibles` WHERE `dni` = :dni AND `deleted_at` IS NULL");
        $stmt->execute(['dni' => $dni]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El DNI ingresado ya se encuentra registrado.");
            return;
        }

        $responsibleId = $this->responsibleModel->create([
            'dni' => $dni,
            'names' => $names,
            'surnames' => $surnames,
            'position' => !empty($position) ? $position : null,
            'office_id' => $office_id,
            'email' => !empty($email) ? $email : null,
            'phone' => !empty($phone) ? $phone : null,
            'status' => $status
        ]);

        if ($responsibleId > 0) {
            $this->response->success("Responsable de custodio registrado correctamente.");
        } else {
            $this->response->error("No se pudo registrar el responsable de custodio.");
        }
    }

    /**
     * API AJAX: Actualizar responsable existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $responsible = $this->responsibleModel->find($id);
        if (!$responsible) {
            $this->response->error("El responsable no existe.", 404);
            return;
        }

        $dni = trim($this->request->input('dni', ''));
        $names = trim($this->request->input('names', ''));
        $surnames = trim($this->request->input('surnames', ''));
        $position = trim($this->request->input('position', ''));
        $office_id = (int)$this->request->input('office_id', 0);
        $email = trim($this->request->input('email', ''));
        $phone = trim($this->request->input('phone', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($dni) || empty($names) || empty($surnames) || empty($office_id)) {
            $this->response->error("Complete todos los campos obligatorios (DNI, Nombres, Apellidos y Oficina).");
            return;
        }

        // Validar unicidad del DNI excluyendo al actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `responsibles` WHERE `dni` = :dni AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['dni' => $dni, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El DNI ingresado ya está registrado por otro responsable.");
            return;
        }

        $updated = $this->responsibleModel->update($id, [
            'dni' => $dni,
            'names' => $names,
            'surnames' => $surnames,
            'position' => !empty($position) ? $position : null,
            'office_id' => $office_id,
            'email' => !empty($email) ? $email : null,
            'phone' => !empty($phone) ? $phone : null,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Responsable de custodio actualizado correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar responsable (Baja lógica con validación de integridad)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $responsible = $this->responsibleModel->find($id);
        if (!$responsible) {
            $this->response->error("El responsable no existe.", 404);
            return;
        }

        // Validación de integridad relacional: no debe estar asignado a ningún bien patrimonial activo
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `responsible_id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar el responsable porque tiene bienes patrimoniales asignados bajo su custodia.");
            return;
        }

        if ($this->responsibleModel->delete($id)) {
            $this->response->success("Responsable eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el responsable.");
        }
    }
}
