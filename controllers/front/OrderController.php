<?php

class OrderController extends OrderControllerCore
{
    public function initContent()
    {
        parent::initContent();

        // Check if the customer is logged in and their account is inactive
        if ($this->context->customer->isLogged() && !$this->context->customer->active) {
            $this->context->smarty->assign('account_confirmation_message', $this->trans(
                'Your account is not verified. Please check your email and confirm your account before proceeding.',
                [],
                'Shop.Notifications.Warning'
            ));
        }
    }
}
