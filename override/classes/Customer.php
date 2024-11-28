<?php

class Customer extends CustomerCore
{
    public static function customerExists($email, $check_active = true, $id_shop = null, &$is_inactive = false)
    {
        if (!Validate::isEmail($email)) {
            return false;
        }

        $query = new DbQuery();
        $query->select('id_customer, active');
        $query->from('customer');
        $query->where('email = "' . pSQL($email) . '"');

        if ($id_shop) {
            $query->where('id_shop = ' . (int)$id_shop);
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

        if ($result) {
            $is_inactive = !$result['active'];
            return (int)$result['id_customer'];
        }

        return false;
    }
}
