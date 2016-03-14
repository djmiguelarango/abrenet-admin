<?php
/**
* Reportes Clientes Ecofuturo
*/
class ReportCLEcofuturoController
{
	private $cx, $sql, $sqlDt, $data, $db, $xls;

	private $row, $rowDt, $rs, $rsDt;

	public $result = '';
	
	function __construct($cx, $data, $db, $xls)
	{
		$this->cx = $cx;
		$this->data = $data;
		$this->db = $db;
		$this->xls = $xls;
	}

	public function setResultEcofuturo()
	{
		if ($this->queryEcofuturo() === true) {
			$swBG = FALSE;
			$arr_state = array('txt' => '', 'txt_bank' => '', 'action' => '', 
				'obs' => '', 'link' => '', 'bg' => '');

			$nCl = 1;
			while ($this->row = $this->rs->fetch_array(MYSQLI_ASSOC)) {
				if ($swBG === FALSE) {
					$bg = 'background: #EEF9F8;';
				} elseif ($swBG === TRUE) {
					$bg = 'background: #D1EDEA;';
				}
							
				$rowSpan = false;
				
				if ($rowSpan === true) {
					$rowSpan = 'rowspan="' . $nCl . '"';
				} elseif ($rowSpan === false) {
					$rowSpan = '';
				} elseif ($rowSpan === 'rowspan="' . $nCl . '"') {
					$rowSpan = 'style="display:none;"';
				}

				if ($this->xls === true) {
					$rowSpan = '';
				}

				$this->result .= rep_client($this->row, $this->rowDt, $this->db, $arr_state, $bg, $rowSpan);

				if ($swBG === false) {
					$swBG = true;
				} elseif ($swBG === true) {
					$swBG = false;
				}
			}
		}
	}

	private function queryEcofuturo()
	{
		$this->sql = "(SELECT 
		    tedc.id_emision AS ide,
		    'Desgravamen' AS r_ramo,
		    tedc.id_emision AS r_no_emision,
		    tedc.prefijo_producto AS r_prefijo,
		    tedc.monto_solicitado AS r_monto_solicitado,
		    (CASE tedc.moneda
		        WHEN 'boliviano' THEN 'Bolivianos'
		        WHEN 'dolar' THEN 'Dolares'
		    END) AS r_moneda,
		    tedc.plazo_credito AS r_plazo,
		    (CASE tedc.tipo_plazo
		        WHEN 'anios' THEN 'Años'
		        WHEN 'meses' THEN 'Meses'
		        WHEN 'semanas' THEN 'Semanas'
		        WHEN 'dias' THEN 'Días'
		    END) AS r_tipo_plazo,
		    tu.nombre AS r_creado_por,
		    tedc.creadopor,
		    DATE_FORMAT(tedc.fecha_creacion, '%d/%m/%Y') AS r_fecha_creacion,
		    tedc.fecha_creacion,
		    tc.id_client AS idCl,
		    CONCAT(tc.nombres,
		            ' ',
		            tc.ap_paterno,
		            ' ',
		            tc.ap_materno) AS cl_nombre,
		    tc.ci_persona AS cl_ci,
		    tc.complemento AS cl_complemento,
		    (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) AS cl_extension,
		    (CASE tc.ciudad_domicilio
		        WHEN 'lapaz' THEN 'La Paz'
		        WHEN 'oruro' THEN 'Oruro'
		        WHEN 'potosi' THEN 'Potosi'
		        WHEN 'tarija' THEN 'Tarija'
		        WHEN 'sucre' THEN 'Chuquisaca'
		        WHEN 'cochabamba' THEN 'Cochabamba'
		        WHEN 'pando' THEN 'Pando'
		        WHEN 'beni' THEN 'Beni'
		        WHEN 'santacruz' THEN 'Santa Cruz'
		    END) AS cl_ciudad,
		    (CASE tc.sexo
		        WHEN 'varon' THEN 'Hombre'
		        WHEN 'mujer' THEN 'Mujer'
		    END) AS cl_genero,
		    tc.tel_domicilio AS cl_telefono,
		    tc.email AS cl_email,
		    (CASE tedp.deudor_codeudor
		        WHEN 'deudor' THEN 'Deudor'
		        WHEN 'codeudor' THEN 'Codeudor'
		    END) AS cl_titular
		FROM
		    tbl_emision_des_cabecera AS tedc
		        INNER JOIN
		    tbl_emision_des_personas AS tedp ON (tedp.id_emision = tedc.id_emision)
		        INNER JOIN
		    tbl_clients AS tc ON (tc.id_client = tedp.id_client)
		        LEFT JOIN
		    tblusuarios AS tu ON (tu.idusuario = tedc.creadopor)
		WHERE
		    tc.ci_persona LIKE '%" . $this->data['dni'] . "%'
		        AND tc.complemento LIKE '%" . $this->data['comp'] . "%'
		        AND (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) LIKE '%" . $this->data['ext'] . "%'
		        AND tedc.fecha_creacion BETWEEN '" . $this->data['date-begin'] . "' 
		        	AND '" . $this->data['date-end'] . "'
		ORDER BY tedc.id_emision DESC) UNION ALL (SELECT 
		    tevc.id_emision AS ide,
		    'Automotores' AS r_ramo,
		    tevc.id_emision AS r_no_emision,
		    tevc.prefijo_producto AS r_prefijo,
		    tevd.valor_asegurado_usd AS r_monto_solicitado,
		    'Dolares' AS r_moneda,
		    tevc.plazo_credito AS r_plazo,
		    (CASE tevc.tipo_plazo
		        WHEN 1 THEN 'Años'
		        WHEN 2 THEN 'Meses'
		        WHEN 3 THEN 'Semanas'
		        WHEN 4 THEN 'Días'
		    END) AS r_tipo_plazo,
		    tu.nombre AS r_creado_por,
		    tevc.user,
		    DATE_FORMAT(tevc.fecha_creacion, '%d/%m/%Y') AS r_fecha_creacion,
		    tevc.fecha_creacion,
		    tc.id_client AS idCl,
		    CONCAT(tc.nombres,
		            ' ',
		            tc.ap_paterno,
		            ' ',
		            tc.ap_materno) AS cl_nombre,
		    tc.ci_persona AS cl_ci,
		    tc.complemento AS cl_complemento,
		    (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) AS cl_extension,
		    (CASE tc.ciudad_domicilio
		        WHEN 'lapaz' THEN 'La Paz'
		        WHEN 'oruro' THEN 'Oruro'
		        WHEN 'potosi' THEN 'Potosi'
		        WHEN 'tarija' THEN 'Tarija'
		        WHEN 'sucre' THEN 'Chuquisaca'
		        WHEN 'cochabamba' THEN 'Cochabamba'
		        WHEN 'pando' THEN 'Pando'
		        WHEN 'beni' THEN 'Beni'
		        WHEN 'santacruz' THEN 'Santa Cruz'
		    END) AS cl_ciudad,
		    (CASE tc.sexo
		        WHEN 'varon' THEN 'Hombre'
		        WHEN 'mujer' THEN 'Mujer'
		    END) AS cl_genero,
		    tc.tel_domicilio AS cl_telefono,
		    tc.email AS cl_email,
		    '' cl_titular
		FROM
		    tbl_emision_vehiculo_cabecera AS tevc
		        INNER JOIN
		    tbl_emision_vehiculo_detalle AS tevd ON (tevd.id_emision = tevc.id_emision)
		        INNER JOIN
		    tbl_clients AS tc ON (tc.id_client = tevc.id_client)
		        LEFT JOIN
		    tblusuarios AS tu ON (tu.idusuario = tevc.user)
		WHERE
		    tc.ci_persona LIKE '%" . $this->data['dni'] . "%'
		        AND tc.complemento LIKE '%" . $this->data['comp'] . "%'
		        AND (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) LIKE '%" . $this->data['ext'] . "%'
		        AND tevc.fecha_creacion BETWEEN '" . $this->data['date-begin'] . "' 
		        	AND '" . $this->data['date-end'] . "'
		ORDER BY tevc.id_emision DESC) UNION ALL (SELECT 
		    tevc.id_emision AS ide,
		    'Automotores' AS r_ramo,
		    tevc.id_emision AS r_no_emision,
		    tevc.prefijo_producto AS r_prefijo,
		    tevd.valor_asegurado_usd AS r_monto_solicitado,
		    'Dolares' AS r_moneda,
		    tevc.plazo_credito AS r_plazo,
		    (CASE tevc.tipo_plazo
		        WHEN 1 THEN 'Años'
		        WHEN 2 THEN 'Meses'
		        WHEN 3 THEN 'Semanas'
		        WHEN 4 THEN 'Días'
		    END) AS r_tipo_plazo,
		    tu.nombre AS r_creado_por,
		    tevc.user,
		    DATE_FORMAT(tevc.fecha_creacion, '%d/%m/%Y') AS r_fecha_creacion,
		    tevc.fecha_creacion,
		    tc.id_client AS idCl,
		    tc.razon_social AS cl_nombre,
		    tc.nit AS cl_ci,
		    '' AS cl_complemento,
		    '' AS cl_extension,
		    (CASE tc.departamento
		        WHEN 'lapaz' THEN 'La Paz'
		        WHEN 'oruro' THEN 'Oruro'
		        WHEN 'potosi' THEN 'Potosi'
		        WHEN 'tarija' THEN 'Tarija'
		        WHEN 'sucre' THEN 'Chuquisaca'
		        WHEN 'cochabamba' THEN 'Cochabamba'
		        WHEN 'pando' THEN 'Pando'
		        WHEN 'beni' THEN 'Beni'
		        WHEN 'santacruz' THEN 'Santa Cruz'
		    END) AS cl_ciudad,
		    '' AS cl_genero,
		    tc.tel_oficina AS cl_telefono,
		    tc.email AS cl_email,
		    '' AS cl_titular
		FROM
		    tbl_emision_vehiculo_cabeceraemp AS tevc
		        INNER JOIN
		    tbl_emision_vehiculo_detalleemp AS tevd ON (tevd.id_emision = tevc.id_emision)
		        INNER JOIN
		    tbl_clientsemp AS tc ON (tc.id_client = tevc.id_client)
		        LEFT JOIN
		    tblusuarios AS tu ON (tu.idusuario = tevc.user)
		WHERE
		    tc.nit LIKE '%" . $this->data['dni'] . "%'
		        AND tevc.fecha_creacion BETWEEN '" . $this->data['date-begin'] . "' 
		        	AND '" . $this->data['date-end'] . "'
		ORDER BY tevc.id_emision DESC) UNION ALL (SELECT 
		    tetc.id_emision AS ide,
		    'Todo Riesgo' AS r_ramo,
		    tetc.id_emision AS r_no_emision,
		    tetc.prefijo_producto AS r_prefijo,
		    tblti.valor_asegurado_usd AS r_monto_solicitado,
		    'Dolares' AS r_moneda,
		    tetc.plazo_credito AS r_plazo,
		    (CASE tetc.tipo_plazo
		        WHEN 1 THEN 'Años'
		        WHEN 2 THEN 'Meses'
		        WHEN 3 THEN 'Semanas'
		        WHEN 4 THEN 'Días'
		    END) AS r_tipo_plazo,
		    tu.nombre AS r_creado_por,
		    tetc.user,
		    DATE_FORMAT(tetc.fecha_creacion, '%d/%m/%Y') AS r_fecha_creacion,
		    tetc.fecha_creacion,
		    tc.id_client AS idCl,
		    CONCAT(tc.nombres,
		            ' ',
		            tc.ap_paterno,
		            ' ',
		            tc.ap_materno) AS cl_nombre,
		    tc.ci_persona AS cl_ci,
		    tc.complemento AS cl_complemento,
		    (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) AS cl_extension,
		    (CASE tc.ciudad_domicilio
		        WHEN 'lapaz' THEN 'La Paz'
		        WHEN 'oruro' THEN 'Oruro'
		        WHEN 'potosi' THEN 'Potosi'
		        WHEN 'tarija' THEN 'Tarija'
		        WHEN 'sucre' THEN 'Chuquisaca'
		        WHEN 'cochabamba' THEN 'Cochabamba'
		        WHEN 'pando' THEN 'Pando'
		        WHEN 'beni' THEN 'Beni'
		        WHEN 'santacruz' THEN 'Santa Cruz'
		    END) AS cl_ciudad,
		    (CASE tc.sexo
		        WHEN 'varon' THEN 'Hombre'
		        WHEN 'mujer' THEN 'Mujer'
		    END) AS cl_genero,
		    tc.tel_domicilio AS cl_telefono,
		    tc.email AS cl_email,
		    '' AS cl_titular
		FROM
		    tbl_emision_tr_cabecera AS tetc
		        LEFT JOIN
		    tbl_emision_tr_vehiculo_detalle AS tetd ON (tetd.id_emision = tetc.id_emision)
		    	INNER JOIN 
		    tbl_tr_inmueble AS tblti ON (tblti.id_emision = tetc.id_emision)
		        INNER JOIN
		    tbl_clients AS tc ON (tc.id_client = tetc.id_client)
		        LEFT JOIN
		    tblusuarios AS tu ON (tu.idusuario = tetc.user)
		WHERE
		    tc.ci_persona LIKE '%" . $this->data['dni'] . "%'
		        AND tc.complemento LIKE '%" . $this->data['comp'] . "%'
		        AND (CASE tc.ci_ext
		        WHEN 'lapaz' THEN 'LP'
		        WHEN 'oruro' THEN 'OR'
		        WHEN 'potosi' THEN 'PT'
		        WHEN 'tarija' THEN 'TJ'
		        WHEN 'sucre' THEN 'CH'
		        WHEN 'cochabamba' THEN 'CB'
		        WHEN 'pando' THEN 'PA'
		        WHEN 'beni' THEN 'BN'
		        WHEN 'santacruz' THEN 'SC'
		    END) LIKE '%" . $this->data['ext'] . "%'
		        AND tetc.fecha_creacion BETWEEN '" . $this->data['date-begin'] . "' 
		        	AND '" . $this->data['date-end'] . "'
		ORDER BY tetc.id_emision DESC) UNION ALL (SELECT 
		    tetc.id_emision AS ide,
		    'Automotores' AS r_ramo,
		    tetc.id_emision AS r_no_emision,
		    tetc.prefijo_producto AS r_prefijo,
		    tblti.valor_asegurado_usd AS r_monto_solicitado,
		    'Dolares' AS r_moneda,
		    tetc.plazo_credito AS r_plazo,
		    (CASE tetc.tipo_plazo
		        WHEN 1 THEN 'Años'
		        WHEN 2 THEN 'Meses'
		        WHEN 3 THEN 'Semanas'
		        WHEN 4 THEN 'Días'
		    END) AS r_tipo_plazo,
		    tu.nombre AS r_creado_por,
		    tetc.user,
		    DATE_FORMAT(tetc.fecha_creacion, '%d/%m/%Y') AS r_fecha_creacion,
		    tetc.fecha_creacion,
		    tc.id_client AS idCl,
		    tc.razon_social AS cl_nombre,
		    tc.nit AS cl_ci,
		    '' AS cl_complemento,
		    '' AS cl_extension,
		    (CASE tc.departamento
		        WHEN 'lapaz' THEN 'La Paz'
		        WHEN 'oruro' THEN 'Oruro'
		        WHEN 'potosi' THEN 'Potosi'
		        WHEN 'tarija' THEN 'Tarija'
		        WHEN 'sucre' THEN 'Chuquisaca'
		        WHEN 'cochabamba' THEN 'Cochabamba'
		        WHEN 'pando' THEN 'Pando'
		        WHEN 'beni' THEN 'Beni'
		        WHEN 'santacruz' THEN 'Santa Cruz'
		    END) AS cl_ciudad,
		    '' AS cl_genero,
		    tc.tel_oficina AS cl_telefono,
		    tc.email AS cl_email,
		    '' AS cl_titular
		FROM
		    tbl_emision_tr_cabeceraemp AS tetc
		        LEFT JOIN
		    tbl_emision_tr_vehiculo_detalleemp AS tetd ON (tetd.id_emision = tetc.id_emision)
		    	INNER JOIN 
		    tbl_tr_inmuebleemp AS tblti ON (tblti.id_emision = tetc.id_emision)
		        INNER JOIN
		    tbl_clientsemp AS tc ON (tc.id_client = tetc.id_client)
		        LEFT JOIN
		    tblusuarios AS tu ON (tu.idusuario = tetc.user)
		WHERE
		    tc.nit LIKE '%" . $this->data['dni'] . "%'
		        AND tetc.fecha_creacion BETWEEN '" . $this->data['date-begin'] . "' 
		        	AND '" . $this->data['date-end'] . "'
		ORDER BY tetc.id_emision DESC)
		;";
		// echo $this->sql;
		if (($this->rs = $this->cx->query($this->sql, MYSQLI_STORE_RESULT)) !== false) {
			if ($this->rs->num_rows > 0) {
				return true;
			}
		}

		return false;
	}

}
?>