<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class MailVerification extends Module
{
    public function __construct()
    {
        $this->name = 'mailverification';
        $this->tab = 'emailing';
        $this->version = '1.0.0';
        $this->author = 'Alberto Cano Jaén';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Mail Verification');
        $this->description = $this->l('Module to handle email verification for user registration.');

        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->module_key = 'mailverificationacjmodule';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('actionCustomerAccountAdd')
            && $this->registerHook('header')
            && $this->createVerificationTable();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->dropVerificationTable();
    }

    private function createVerificationTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'customer_verification` (
            `id_verification` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_customer` INT(11) UNSIGNED NOT NULL,
            `token` VARCHAR(255) NOT NULL,
            `date_add` DATETIME NOT NULL,
            PRIMARY KEY (`id_verification`),
            UNIQUE KEY `unique_token` (`token`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
        return Db::getInstance()->execute($sql);
    }

    private function dropVerificationTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'customer_verification`';
        return Db::getInstance()->execute($sql);
    }

    public function hookHeader()
    {
        $controller = $this->context->controller->php_self;

        if (in_array($controller, ['authentication', 'order'])) {
            $this->context->controller->addJS($this->_path . 'views/js/form-handler.js');
            $this->context->controller->addCSS($this->_path . 'views/css/form-loader.css');
        }
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $customer = $params['newCustomer'];
        $token = md5(uniqid(mt_rand(), true));

        Db::getInstance()->insert('customer_verification', [
            'id_customer' => (int) $customer->id,
            'token' => pSQL($token),
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        $verificationLink = $this->context->link->getModuleLink(
            $this->name,
            'verify',
            ['token' => $token]
        );

        Mail::Send(
            (int)$this->context->language->id,
            'account_verification',
            $this->trans('Account Verification', [], 'Emails.Subject'),
            [
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{verification_link}' => $verificationLink,
            ],
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            null,
            null,
            _PS_MAIL_DIR_,
            false,
            $this->context->shop->id
        );

        Db::getInstance()->update('customer', ['active' => 0], 'id_customer = ' . (int)$customer->id);
    }
}
