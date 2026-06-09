<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\FundingSource;

class FundingSourceController extends Controller
{
    private FundingSource $fundingSourceModel;

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        $this->fundingSourceModel = new FundingSource();
    }

    /**
     * Vista principal del CRUD de Fuentes de Financiamiento
     */
    public function index()
    {
        $this->authorize('ROLE_VIEW');

        $sources = $this->fundingSourceModel->all();

        $this->render('funding_sources/index', [
            'title' => 'Fuentes de Financiamiento',
            'sources' => $sources
        ]);
    }

    /**
     * API AJAX: Detalle de una fuente de financiamiento
     */
    public function ajaxDetail(array $params)
    {
        $this->authorize('ROLE_VIEW');
        $id = (int)$params['id'];

        $source = $this->fundingSourceModel->find($id);

        if (!$source) {
            $this->response->error("La fuente de financiamiento no existe.", 404);
            return;
        }

        $this->response->success("Detalle obtenido correctamente.", $source);
    }

    /**
     * API AJAX: Guardar nueva fuente de financiamiento
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

        // Validar código único en registros activos
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `funding_sources` WHERE `code` = :code AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código de la fuente de financiamiento ya se encuentra registrado.");
            return;
        }

        $sourceId = $this->fundingSourceModel->create([
            'code' => $code,
            'name' => $name,
            'status' => $status
        ]);

        if ($sourceId > 0) {
            $this->response->success("Fuente de financiamiento registrada correctamente.");
        } else {
            $this->response->error("No se pudo registrar la fuente de financiamiento.");
        }
    }

    /**
     * API AJAX: Actualizar fuente de financiamiento existente
     */
    public function ajaxUpdate(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $source = $this->fundingSourceModel->find($id);
        if (!$source) {
            $this->response->error("La fuente de financiamiento no existe.", 404);
            return;
        }

        $code = trim($this->request->input('code', ''));
        $name = trim($this->request->input('name', ''));
        $status = (int)$this->request->input('status', 1);

        if (empty($code) || empty($name)) {
            $this->response->error("Complete todos los campos obligatorios (Código y Nombre).");
            return;
        }

        // Validar código único excluyendo al actual
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `funding_sources` WHERE `code` = :code AND `id` != :id AND `deleted_at` IS NULL");
        $stmt->execute(['code' => $code, 'id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("El código de la fuente de financiamiento ya está registrado por otra fuente.");
            return;
        }

        $updated = $this->fundingSourceModel->update($id, [
            'code' => $code,
            'name' => $name,
            'status' => $status
        ]);

        if ($updated) {
            $this->response->success("Fuente de financiamiento actualizada correctamente.");
        } else {
            $this->response->error("No se detectaron cambios o no se pudo actualizar.");
        }
    }

    /**
     * API AJAX: Eliminar fuente de financiamiento (Baja lógica con validación de dependencias)
     */
    public function ajaxDelete(array $params)
    {
        $this->authorize('ROLE_EDIT');
        $id = (int)$params['id'];

        $source = $this->fundingSourceModel->find($id);
        if (!$source) {
            $this->response->error("La fuente de financiamiento no existe.", 404);
            return;
        }

        // Validar si tiene bienes patrimoniales asignados activos
        $db = \App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM `assets` WHERE `funding_source_id` = :id AND `deleted_at` IS NULL");
        $stmt->execute(['id' => $id]);
        if ($stmt->fetchColumn() > 0) {
            $this->response->error("No se puede eliminar la fuente de financiamiento porque tiene bienes patrimoniales asociados activos.");
            return;
        }

        if ($this->fundingSourceModel->delete($id)) {
            $this->response->success("Fuente de financiamiento eliminada correctamente.");
        } else {
            $this->response->error("No se pudo eliminar la fuente de financiamiento.");
        }
    }
}
