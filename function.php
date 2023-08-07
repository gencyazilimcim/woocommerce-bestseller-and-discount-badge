<?php

add_action('woocommerce_before_shop_loop_item_title', 'vono_bestseller_discount_badge_product_loop', 10);

function vono_bestseller_discount_badge_product_loop()
{
    if (is_archive()) :
        global $product;
        global $wp_query;

        $post_ids = wp_list_pluck($wp_query->posts, 'ID');

        // best seller
        $args = array(
            'post_type'       => 'product',
            'post__in'        => $post_ids,
            'post_status'     => 'publish',
            'posts_per_page'  => -1,
            'orderby'         => 'meta_value_num',
            'order'           => 'DESC',
            'fields'          => 'ids',
            // 'meta_key'      => 'total_sales',
            'meta_query'      => array(
                array(
                    'key'         => 'total_sales',
                    'value'       => 20, // satış adedi 20'den yüksek olanlar
                    'compare'     => '>',
                    // 'type'      => 'numeric',
                )
            )
        );
        $bestseller = new WP_Query($args);
        // best seller


        // discount check
        if (!$product->is_on_sale()) return;

        if ($product->is_type('simple')) {
            $max_percentage = (($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100;
        } elseif ($product->is_type('variable')) {
            $max_percentage = 0;
            $prices = $product->get_variation_prices(true); // this is the fastest way to get the prices for all variants - returns multidimensional array from object cache
            foreach ($prices['regular_price'] as $pid => $regular_price) {
                if ($regular_price == 0) continue; // if regular price is 0, skip this product
                if ($regular_price == $prices['sale_price'][$pid]) continue; // if sale price = regular price, skip this product
                $percentage = ($regular_price - $prices['sale_price'][$pid]) / $regular_price * 100;
                if ($percentage > $max_percentage) {
                    $max_percentage = $percentage;
                }
            }
        } else {
            $max_percentage = false;
        }
        // discount check

        //output
        if (in_array($product->get_id(), $bestseller->posts)) {
            echo '<img class="onsale" src="http://localhost/trywordpress/wp-content/uploads/2023/07/badge_63f305dc2ab576-19413580-95794290.png" style="width:65px; height:65px"> ';
        } elseif (round($max_percentage) == 15) {
            echo '<img class="onsale" src="http://localhost/trywordpress/wp-content/uploads/2023/07/badge_6401dc09ccafd5-45188287-53511273.png" style="width:65px; height:65px"> ';
        } else {
            echo ($max_percentage)  ? '<img class="onsale" src="http://localhost/trywordpress/wp-content/uploads/2023/07/badge_63f4d189d09445-36821477-93897122.png" style="width:65px; height:65px"> ' : "";
        }
    endif;
}
