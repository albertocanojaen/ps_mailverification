<?php

class AuthController extends AuthControllerCore
{
    public function postProcess()
    {
        if (Tools::isSubmit('submitCreate')) {
            $email = Tools::getValue('email');
            $password = Tools::getValue('password');

            // Validate Email
            if (!Validate::isEmail($email)) {
                $this->errors[] = $this->trans('Invalid email address.', [], 'Shop.Notifications.Error');
                return;
            }

            // Validate Password
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
                $this->errors[] = $this->trans(
                    'The password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, and one number.',
                    [],
                    'Shop.Notifications.Error'
                );
                return;
            }

            // Check if Email Exists
            if (Customer::customerExists($email)) {
                $this->errors[] = $this->trans(
                    'This email is already registered. Please log in or use a different email.',
                    [],
                    'Shop.Notifications.Error'
                );
                return;
            }

            // Create Customer
            $customer = new Customer();
            $customer->firstname = Tools::getValue('firstname');
            $customer->lastname = Tools::getValue('lastname');
            $customer->email = $email;
            $customer->passwd = Tools::encrypt($password); // Use PrestaShop's encryption
            $customer->active = 0; // Set as inactive for email verification

            if ($customer->add()) {
                // Generate and send verification email
                $this->generateAndSendVerification($customer);

                // Set a session flag to show the message
                $this->context->cookie->registration_success = true;

                // Redirect with a success parameter
                Tools::redirect('index.php?controller=authentication&account_created=1');
            } else {
                $this->errors[] = $this->trans(
                    'There was an error creating your account. Please try again later.',
                    [],
                    'Shop.Notifications.Error'
                );
            }
        }

        parent::postProcess();
    }

    private function generateAndSendVerification(Customer $customer)
    {
        // Generate a unique token
        $token = md5(uniqid(mt_rand(), true));

        // Insert token into the database
        Db::getInstance()->insert('customer_verification', [
            'id_customer' => (int)$customer->id,
            'token' => pSQL($token),
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        // Create verification link
        $verificationLink = $this->context->link->getModuleLink(
            'mailverification',
            'verify',
            ['token' => $token],
            true
        );

        // Send email
        Mail::Send(
            (int)$this->context->language->id,
            'account_verification',
            $this->trans('Verify your account', [], 'Emails.Subject'),
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
    }

    public function initContent()
    {
        parent::initContent();

        // Display the message if the account was recently created
        if (isset($this->context->cookie->registration_success)) {
            $this->context->smarty->assign('confirmation_message', $this->trans(
                'Your account has been created. Please check your email to confirm your account before proceeding.',
                [],
                'Shop.Notifications.Success'
            ));

            // Clear the session flag
            unset($this->context->cookie->registration_success);
        }
    }
}
