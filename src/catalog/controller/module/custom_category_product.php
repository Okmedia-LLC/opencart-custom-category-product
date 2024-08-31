<?php
namespace Opencart\Catalog\Controller\Extension\CustomCategoryProduct\Module;
/**
 * Class CustomCategoryProduct
 *
 * @package
 */
class CustomCategoryProduct extends \Opencart\System\Engine\Controller {
    /**
     * @return string
     */
    public function index(array $setting): string {
        $this->load->language("extension/custom_category_product/module/custom_category_product");
        
        $this->load->model("catalog/category");
        $this->load->model("catalog/product");
        $this->load->model("extension/custom_category_product/module/custom_category_product");
        $this->load->model("tool/image");
        
        $data["categories"] = [];
        
        if (!empty($setting["category"])) {
            $categories = [];
            
            foreach ($setting["category"] as $category_id) {
                $category_info = $this->model_catalog_category->getCategory( $category_id );
                
                if ($category_info) $categories[] = $category_info;
            }
            
            $width = !empty($setting["width"]) ? $setting["width"] : -1;
            $height = !empty($setting["height"]) ? $setting["height"] : -1;
            
            $data['add_to_wishlist'] = $this->url->link('account/wishlist.add', 'language=' . $this->config->get('config_language'));
            
            foreach ($categories as $category) {
                
                if ($category["image"]) 
                    $image = $this->model_tool_image->resize( $category["image"], $width, $height );
                else 
                    $image = $this->model_tool_image->resize( "placeholder.png", $width, $height );
                
                if (VERSION >= "4.0.2.0") 
                    $description = oc_substr( html_entity_decode( $category["description"], ENT_QUOTES, "UTF-8" ), 0 );
                else
                    $description = Helper\utf8\substr( html_entity_decode( $category["description"], ENT_QUOTES, "UTF-8" ), 0 );
                    
                $products_ids = $this->model_extension_custom_category_product_module_custom_category_product->getCategoryProductIds($category["category_id"]);
                
                $products_infoes = [];
                
                foreach ($products_ids as $product) {
                    $product_info = $this->model_catalog_product->getProduct((int)$product['product_id']);
                    
                    if ($product_info) $products_infoes[] = $product_info;
                }
                
                $products = [];
                
                foreach ($products_infoes as $product) {
                    
                    if ($product["image"]) 
                        $image = $this->model_tool_image->resize( html_entity_decode( $product["image"], ENT_QUOTES, "UTF-8" ), $width, $height );
                    else
                        $image = $this->model_tool_image->resize( "placeholder.png", $width, $height );
                        
                    if ( $this->customer->isLogged() || !$this->config->get("config_customer_price") )
                        $price = $this->currency->format( $this->tax->calculate( $product["price"], $product["tax_class_id"], $this->config->get("config_tax") ), $this->session->data["currency"] );
                    else
                        $price = false;
                    
                    if ((float) $product["special"]) 
                        $special = $this->currency->format( $this->tax->calculate( $product["special"], $product["tax_class_id"], $this->config->get("config_tax") ), $this->session->data["currency"] );
                    else
                        $special = false;
                    
                    if ($this->config->get("config_tax")) 
                        $tax = $this->currency->format( (float) $product["special"] ? $product["special"] : $product["price"], $this->session->data["currency"] );
                    else 
                        $tax = false;
                    
                    $options = [];
                    
                    $product_options = $this->model_catalog_product->getOptions($product["product_id"]);
                    
                    foreach ($product_options as $option) {
                        if (!isset($product_info['override']['variant'][$option['product_option_id']])) {
                            $product_option_value_data = [];
                        
                            foreach ($option['product_option_value'] as $option_value) {
                                if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                                    
                                    if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price'])
                                        $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
                                    else 
                                        $price = false;
                                        
                                    if (is_file(DIR_IMAGE . html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'))) 
                                        $image_opt = $this->model_tool_image->resize(html_entity_decode($option_value['image'], ENT_QUOTES, 'UTF-8'), 50, 50);
                                    else
                                        $image_opt = '';
                                        
                                    $product_option_value_data[] = [
                                        'product_option_value_id'  => $option_value['product_option_value_id'],
                                        'option_value_id'          => $option_value['option_value_id'],
                                        'name'                     => $option_value['name'],
                                        'image'                    => $image_opt,
                                        'price'                    => $price,
                                        'price_prefix'             => $option_value['price_prefix']
                                    ];
                                }
                            }
                            
                            $options[] = [
                                'product_option_id'     => $option['product_option_id'],
                                'product_option_value'  => $product_option_value_data,
                                'option_id'             => $option['option_id'],
                                'name'                  => $option['name'],
                                'type'                  => $option['type'],
                                'value'                 => $option['value'],
                                'required'              => $option['required']
                            ];
                        }
                    }
                    
                    $product_data = [
                        "product_id"    => $product["product_id"],
                        "thumb"         => $image,
                        "name"          => $product["name"],
                        "description"   => oc_substr( trim( strip_tags( html_entity_decode( $product["description"], ENT_QUOTES, "UTF-8" ) ) ), 0, $this->config->get( "config_product_description_length" ) ) . "..",
                        "price"         => $price,
                        "special"       => $special,
                        "options"       => $options,
                        "tax"           => $tax,
                        "minimum"       => $product["minimum"] > 0 ? $product["minimum"] : 1,
                        "rating"        => (int) $product["rating"],
                        "href"          => $this->url->link( "product/product", "language=" . $this->config->get("config_language") . "&product_id=" . $product["product_id"] ),
                    ];
                    
                    $products[] = $product_data;
                }
                
                $data["categories"][] = [
                    "category_id"       => $category["category_id"],
                    "name"              => $category["name"],
                    "description"       => $description,
                    "thumb"             => $image,
                    "alt"               => $category["name"] . " image",
                    "href"              => $this->url->link( "product/category", "language=" . $this->config->get("config_language") . "&path=" . $category["category_id"] ),
                    "products"          => $products,
                ];
            }
        }
        
        if ($data["categories"]) {
            $route = "extension/custom_category_product/module/";
            
            if ( !empty($setting["twig_name"]) && is_file( DIR_EXTENSION . "/custom_category_product/catalog/view/template/module/" . $setting["twig_name"] . ".twig" ) )
                $route .= $setting["twig_name"];
            else
                $route .= "custom_category_product";
            
            return $this->load->view($route, $data);
        } else {
            return "";
        }
    }
}
