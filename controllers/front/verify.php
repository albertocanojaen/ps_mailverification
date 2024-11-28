<?php

class MailVerificationVerifyModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Get the token from the URL
        $token = Tools::getValue('token');

        // Look for the token in the database
        $verification = Db::getInstance()->getRow(
            'SELECT id_customer FROM ' . _DB_PREFIX_ . 'customer_verification WHERE token = "' . pSQL($token) . '"'
        );

        if ($verification) {
            // Activate the customer account
            Db::getInstance()->update(
                'customer',
                ['active' => 1],
                'id_customer = ' . (int)$verification['id_customer']
            );

            // Delete the verification token
            Db::getInstance()->delete('customer_verification', 'token = "' . pSQL($token) . '"');

            // Assign success message to the template
            $this->context->smarty->assign('success', true);
        } else {
            // Assign error message to the template
            $this->context->smarty->assign('errors', ['Invalid or expired verification token.']);
        }

        // Load the template
        $this->setTemplate('module:mailverification/views/templates/front/verify.tpl');
    }
}
