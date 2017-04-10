<?php

abstract class b2c_goods_check_abstract
{
    public function check_product_delete($product_id, &$msg)
    {
        return true;
    }

    public function check_goods_delete($goods_id, &$msg)
    {
        return true;
    }

    public function check_goods_marketable_false($goods_ids, &$msg)
    {
        return true;
    }
}
