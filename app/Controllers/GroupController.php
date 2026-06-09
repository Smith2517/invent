<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Group;
use App\Models\Subgroup;

class GroupController extends Controller
{
    private Group $groupModel;
    private Subgroup $subgroupModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->groupModel = new Group();
        $this->subgroupModel = new Subgroup();
    }

    /**
     * Listar grupos y subgrupos en la pestaña unificada
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');

        $groups = $this->groupModel->all();
        
        // Obtener subgrupos con el nombre del grupo
        $db = \App\Core\Database::getConnection();
        $stmt = $db->query("
            SELECT s.*, g.description as group_name 
            FROM `subgroups` s 
            JOIN `groups` g ON s.group_id = g.id 
            WHERE s.deleted_at IS NULL AND g.deleted_at IS NULL
            ORDER BY g.code, s.code
        ");
        $subgroups = $stmt->fetchAll();

        $this->render('groups/index', [
            'title' => 'Grupos y Subgrupos',
            'groups' => $groups,
            'subgroups' => $subgroups
        ]);
    }

    /**
     * API AJAX: Obtener detalle de grupo
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];
        $group = $this->groupModel->find($id);

        if (!$group) {
            $this->response->error("El grupo no existe.", 404);
            return;
        }

        $this->response->success("Detalle de grupo obtenido", $group);
    }

    /**
     * API AJAX: Guardar nuevo grupo
     */
    public function ajaxSave()
    {
        $this->authorize('ROLE_EDIT');

        $code = $this->request->input('code', '');
        $description = $this->request->input('description', '');
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($description)) {
            $this->response->error("Complete todos los campos obligatorios.");
            return;
        }

        // Verificar código único
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `groups` WHERE `code` = :code AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del grupo ya se encuentra registrado.");
            return;
        }

        $groupId = $this->groupModel->create([
            'code' => $code,
            'description' => $description,
            'status' => $status
        ]);

        if ($groupId > 0) {
            $this->response->success("Grupo registrado correctamente.");
        } else {
            $this->response->error("No se pudo registrar el grupo.");
        }
    }

    /**
     * API AJAX: Actualizar grupo
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $group = $this->groupModel->find($id);
        if (!$group) {
            $this->response->error("El grupo no existe.", 404);
            return;
        }

        $code = $this->request->input('code', '');
        $description = $this->request->input('description', '');
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($description)) {
            $this->response->error("Complete todos los campos obligatorios.");
            return;
        }

        // Verificar código único excluyendo el actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `groups` WHERE `code` = :code AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del grupo ya está registrado en otro grupo.");
            return;
        }

        $updated = $this->groupModel->update($id, [
            'code' => $code,
            'description' => $description,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Grupo actualizado correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar grupo (Baja lógica)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $group = $this->groupModel->find($id);
        if (!$group) {
            $this->response->error("El grupo no existe.", 404);
            return;
        }

        // Verificar si tiene subgrupos asociados
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `subgroups` WHERE `group_id` = :group_id AND `deleted_at` IS NULL");
        $stmt->execute(['group_id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar el grupo porque tiene subgrupos asociados activos.");
            return;
        }

        if ($this->groupModel->delete($id)) {
            $this->response->success("Grupo eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el grupo.");
        }
    }
}
