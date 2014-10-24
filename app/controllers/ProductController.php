<?php
/**
* ReportesGenerales
*/
require_once '/../config/administrator.php';

class ProductController extends Administrator
{
	public 
		$id,
		$producto,
		$codigo,

		$data = array(),
		$number = 0,
		$type;
	
	public function __construct($type)
	{
		parent::__construct();
		$this->type = $type;
	}

	public function getProduct()
	{
		$sql = 'select 
			sp.id,
			sp.nombre as producto,
			sp.codigo
		from 
			sa_producto as sp 
		order by sp.id asc
		;';

		if (($rs = $this->query($sql, MYSQLI_STORE_RESULT)) !== false) {
			$this->number = $rs->num_rows;
			if ($this->number > 0) {
				$this->data = array();

				while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
					$this->data[] = $row;
				}
				return true;
			}
		}

		return false;
	}

	public function getEFProduct($pr)
	{
		$sql = 'select 
			sef.id as ef_id,
			sef.nombre as ef_nombre,
			sef.codigo as ef_codigo
		from 
			sa_entidad_financiera as sef
				inner join
			sa_ef_producto as sep ON (sep.entidad_financiera = sef.id)
				inner join
			sa_producto as spr ON (spr.id = sep.producto)
		where
			spr.codigo = "' . $pr . '"
		;';

		if (($rs = $this->query($sql, MYSQLI_STORE_RESULT)) !== false) {
			if ($rs->num_rows > 0) {
				$this->data = array();
				while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
					$this->data[] = $row;
				}

				return true;
			}
		}

		return false;
	}

	public function getInsurer($id = '')
	{
		$sql = 'select 
			sa.id as as_id,
			sa.nombre as as_nombre,
			sa.codigo as as_codigo
		from 
			sa_aseguradora as sa
		where
			sa.id like "%' . $id . '%"
		;';

		if (($rs = $this->query($sql, MYSQLI_STORE_RESULT)) !== false) {
			if ($rs->num_rows > 0) {
				$this->data = array();
				while ($row = $rs->fetch_array(MYSQLI_ASSOC)) {
					$this->data[] = $row;
				}

				return true;
			}
		}

		return false;
	}

}
?>