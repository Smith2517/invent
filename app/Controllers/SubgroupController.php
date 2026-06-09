<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subgroup;

class SubgroupController extends Controller
{
    private Subgroup $subgroupModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->subgroupModel = new Subgroup();
    }

    /**
     * Retornar los subgrupos de un grupo específico en formato JSON (AJAX)
     */
    public function byGroup(array $params)
    {
        $groupId = (int)$params['group_id'];
        
        if ($groupId <= 0) {
            $this->json([]);
            return;
        }

        $subgroups = $this->subgroupModel->getByGroupId($groupId);
        $this->json($subgroups);
    }

    /**
     * API AJAX: Obtener detalle de subgrupo
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];
        $subgroup = $this->subgroupModel->find($id);

        if (!$subgroup) {
            $this->response->error("El subgrupo no existe.", 404);
            return;
        }

        $this->response->success("Detalle de subgrupo obtenido", $subgroup);
    }

    /**
     * API AJAX: Guardar nuevo subgrupo
     */
    public function ajaxSave()
    {
        $this->authorize('ROLE_EDIT');

        $groupId = (int)$this->request->input('group_id', 0);
        $code = $this->request->input('code', '');
        $description = $this->request->input('description', '');
        $status = (int)$this->request->input('status', 1);

        if ($groupId <= 0 || empty($code) || empty($description)) {
            $this->response->error("Complete todos los campos obligatorios.");
            return;
        }

        // Verificar si el grupo padre existe
        $groupModel = new \App\Models\Group();
        if (!$groupModel->find($groupId)) {
            $this->response->error("El grupo genérico seleccionado no existe.");
            return;
        }

        // Verificar si el código ya existe en el mismo grupo genérico
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `subgroups` WHERE `code` = :code AND `group_id` = :group_id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'group_id' => $groupId]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del subgrupo ya está registrado en este grupo.");
            return;
        }

        $subgroupId = $this->subgroupModel->create([
            'group_id' => $groupId,
            'code' => $code,
            'description' => $description,
            'status' => $status
        ]);

        if ($subgroupId > 0) {
            $this->response->success("Subgrupo registrado correctamente.");
        } else {
            $this->response->error("No se pudo registrar el subgrupo.");
        }
    }

    /**
     * API AJAX: Actualizar subgrupo
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $subgroup = $this->subgroupModel->find($id);
        if (!$subgroup) {
            $this->response->error("El subgrupo no existe.", 404);
            return;
        }

        $groupId = (int)$this->request->input('group_id', 0);
        $code = $this->request->input('code', '');
        $description = $this->request->input('description', '');
        $status = (int)$this->request->input('status', 1);

        if ($groupId <= 0 || empty($code) || empty($description)) {
            $this->response->error("Complete todos los campos obligatorios.");
            return;
        }

        // Verificar si el grupo padre existe
        $groupModel = new \App\Models\Group();
        if (!$groupModel->find($groupId)) {
            $this->response->error("El grupo genérico seleccionado no existe.");
            return;
        }

        // Verificar si el código ya existe en el mismo grupo genérico, excluyendo el actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `subgroups` WHERE `code` = :code AND `group_id` = :group_id AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'group_id' => $groupId, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código del subgrupo ya está registrado en este grupo.");
            return;
        }

        $updated = $this->subgroupModel->update($id, [
            'group_id' => $groupId,
            'code' => $code,
            'description' => $description,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Subgrupo actualizado correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar subgrupo (Baja lógica)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $subgroup = $this->subgroupModel->find($id);
        if (!$subgroup) {
            $this->response->error("El subgrupo no existe.", 404);
            return;
        }

        // Verificar si hay bienes patrimoniales asignados a este subgrupo
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `subgroup_id` = :subgroup_id AND `deleted_at` IS NULL");
        $stmt->execute(['subgroup_id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar el subgrupo porque está asignado a bienes patrimoniales activos.");
            return;
        }

        if ($this->subgroupModel->delete($id)) {
            $this->response->success("Subgrupo eliminado correctamente.");
        } else {
            $this->response->error("No se pudo eliminar el subgrupo.");
        }
    }
}
