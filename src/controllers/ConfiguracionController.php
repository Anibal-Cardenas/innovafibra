<?php
/**
 * Controlador de Configuración del Sistema
 */

class ConfiguracionController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->checkRole(ROL_ADMINISTRADOR);
    }

    /**
     * Mostrar parámetros de configuración
     */
    public function index() {
        // Ensure `factor_conversion_bolsas` exists with a default value
        try {
            $check = $this->db->prepare("SELECT 1 FROM configuracion_sistema WHERE parametro = ? LIMIT 1");
            $check->execute(['factor_conversion_bolsas']);
            $exists = $check->fetch();

            // Prepare insert statement once so it can be reused below
            $insert = $this->db->prepare(
                "INSERT INTO configuracion_sistema (parametro, valor, tipo_dato, descripcion) VALUES (?, ?, ?, ?)"
            );

            if (!$exists) {
                $insert->execute([
                    'factor_conversion_bolsas',
                    (string)DEFAULT_FACTOR_CONVERSION,
                    'decimal',
                    'Factor de conversión (bolsas por kg) para calcular cantidad de bolsas plásticas al comprar'
                ]);
            }
            // Ensure separate factor for cubo -> bolsas estimation exists
            $check2 = $this->db->prepare("SELECT 1 FROM configuracion_sistema WHERE parametro = ? LIMIT 1");
            $check2->execute(['factor_conversion_cubo']);
            $exists2 = $check2->fetch();
            if (!$exists2) {
                $insert->execute([
                    'factor_conversion_cubo',
                    (string)DEFAULT_FACTOR_CONVERSION_CUBO,
                    'decimal',
                    'Factor (bolsas por kg) para estimar bolsas a partir del peso del cubo de fibra'
                ]);
            }

            // Inicializar parámetros de empresa para impresión (Comprobantes y Guías)
            $paramsEmpresa = [
                ['nombre_empresa', APP_NAME, 'string', 'Nombre de la empresa (aparece en comprobantes y guías)'],
                ['ruc_empresa', '00000000000', 'string', 'RUC de la empresa'],
                ['direccion_empresa', 'Dirección del Taller', 'string', 'Dirección fiscal o del taller'],
                ['telefono_empresa', '999 999 999', 'string', 'Teléfono de contacto'],
                ['email_empresa', 'contacto@napa.com', 'string', 'Email de contacto']
            ];

            foreach ($paramsEmpresa as $p) {
                $check = $this->db->prepare("SELECT 1 FROM configuracion_sistema WHERE parametro = ? LIMIT 1");
                $check->execute([$p[0]]);
                if (!$check->fetch()) {
                    $insert->execute($p);
                }
            }
        } catch (PDOException $e) {
            // ignore and continue; the param may be missing in DB schema
        }

        $stmt = $this->db->query(
            "SELECT parametro, valor, tipo_dato, descripcion FROM configuracion_sistema ORDER BY parametro"
        );
        $params = $stmt->fetchAll();

        $data = [
            'title' => 'Configuración del Sistema',
            'params' => $params
        ];

        $this->view('configuracion/index', $data);
    }

    /**
     * Guardar cambios en la configuración
     */
    public function guardar() {
        if (!$this->isPost()) {
            redirect(BASE_URL . '/configuracion');
        }

        if (!verifyCsrfToken($this->post('csrf_token'))) {
            setFlashMessage(MSG_ERROR, 'Token de seguridad inválido');
            redirect(BASE_URL . '/configuracion');
        }

        $valores = $this->post('valor'); // array parametro => valor
        $motivo = trim($this->post('motivo'));

        if (empty($valores) || !is_array($valores)) {
            setFlashMessage(MSG_ERROR, 'No se recibieron parámetros para actualizar');
            redirect(BASE_URL . '/configuracion');
        }

        try {
            $this->db->beginTransaction();

            $updateStmt = $this->db->prepare(
                "UPDATE configuracion_sistema SET valor = ?, usuario_modificacion = ?, fecha_modificacion = NOW() WHERE parametro = ?"
            );

            $histStmt = $this->db->prepare(
                "INSERT INTO historial_configuracion (parametro, valor_anterior, valor_nuevo, usuario_cambio, motivo) VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($valores as $param => $nuevoValor) {
                // Obtener valor anterior
                $stmt = $this->db->prepare("SELECT valor FROM configuracion_sistema WHERE parametro = ? LIMIT 1");
                $stmt->execute([$param]);
                $row = $stmt->fetch();
                $valorAnterior = $row ? $row['valor'] : null;

                // Actualizar
                $updateStmt->execute([$nuevoValor, getCurrentUserId(), $param]);

                // Insertar historial
                $histStmt->execute([$param, $valorAnterior, $nuevoValor, getCurrentUserId(), $motivo]);

                // Auditoría
                registrarAuditoria('configuracion_sistema', null, AUDITORIA_UPDATE, "Actualizado parámetro {$param}", $valorAnterior, $nuevoValor);
            }

            $this->db->commit();

            setFlashMessage(MSG_SUCCESS, 'Configuración actualizada correctamente');
            redirect(BASE_URL . '/configuracion');

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al actualizar configuración: ' . $e->getMessage());
            setFlashMessage(MSG_ERROR, 'Error al guardar la configuración');
            redirect(BASE_URL . '/configuracion');
        }
    }
}
