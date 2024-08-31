<?php
namespace Opencart\Catalog\Model\Extension\CustomCategoryProduct\Module;
/**
 * Class CustomCategoryProduct
 *
 * @package Opencart\Catalog\Model\Extension\CustomCategoryProduct\Module
 */
class CustomCategoryProduct extends \Opencart\System\Engine\Model {

	/**
	 * @param int $category_id
	 *
	 * @return array
	 */
	public function getCategoryProductIds(int $category_id): array {
		$query = $this->db->query("SELECT DISTINCT p.`product_id` FROM `" . DB_PREFIX . "product` p INNER JOIN `" . DB_PREFIX . "product_to_category` ptc ON (p.`product_id` = ptc.`product_id`) WHERE ptc.`category_id` = '" . (int)$category_id . "'");
        
		return $query->rows;
	}
}
