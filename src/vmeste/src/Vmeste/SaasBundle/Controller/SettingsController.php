<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 9/23/14
 * Time: 9:44 PM
 */

namespace Vmeste\SaasBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vmeste\SaasBundle\Entity\Settings;
use Vmeste\SaasBundle\Entity\User;
use Vmeste\SaasBundle\Util\Hash;

class SettingsController extends Controller
{

    /**
     * @Template
     */
    public function editSettingsAction()
    {
        $userId = $this->getRequest()->get('userId', null);


        $emailSettingsErrors = null;
        if ($this->getRequest()->query->get("email_setting_errors") != null)
            $emailSettingsErrors = $this->getRequest()->query->get("email_setting_errors");

        $passwordSettingsErrors = null;
        if ($this->getRequest()->query->get("password_setting_errors") != null)
            $passwordSettingsErrors = $this->getRequest()->query->get("password_setting_errors");

        if ($userId == null) {
            $authorizedUser = $this->get('security.context')->getToken()->getUser();
            $userId = $authorizedUser->getId();
        }

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => intval($userId)));

        if ($user == null) {
            return $this->redirect($this->generateUrl("vmeste_saas"));
        } else {
            $settingsCollection = $user->getSettings();
            $userSettings = $settingsCollection[0];
            $yandexKassa = $userSettings->getYandexKassa();
        }


        $updateEmailSettingsRoute = $this->generateUrl('update_email_settings');
        $updateYKSettingsRoute = $this->generateUrl('update_yk_settings');
        $updatePasswordRoute = $this->generateUrl('update_customer_password');

        $userIdForEdit = null;

        if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
            $updateEmailSettingsRoute = $this->generateUrl('admin_update_email_settings');
            $updateYKSettingsRoute = $this->generateUrl('admin_update_yk_settings');
            $updatePasswordRoute = $this->generateUrl('admin_update_customer_password');
            $userIdForEdit = $userId;
        }

        return array(
            'email_setting_errors' => $emailSettingsErrors,
            'notification_email' => $userSettings->getNotificationEmail(),
            'sender_name' => $userSettings->getSenderName(),
            'sender_email' => $userSettings->getSenderEmail(),
            'csv_separator' => $userSettings->getCsvColumnSeparator(),
            'shopid' => $yandexKassa->getShopId(),
            'scid' => $yandexKassa->getScid(),
            'shoppw' => $yandexKassa->getShoppw(),
            'pc' => $yandexKassa->getPc(),
            'ac' => $yandexKassa->getAc(),
            'wm' => $yandexKassa->getWm(),
            'mc' => $yandexKassa->getMc(),
            'gp' => $yandexKassa->getGp(),
            'sandbox' => $yandexKassa->getSandbox(),
            'password_setting_errors' => $passwordSettingsErrors,
            'updateEmailSettingsRoute' => $updateEmailSettingsRoute,
            'updateYKSettingsRoute' => $updateYKSettingsRoute,
            'updatePasswordRoute' => $updatePasswordRoute,
            'userIdForEdit' => $userIdForEdit,
        );
    }

    public function updateEmailSettingsAction()
    {
        $request = $this->getRequest();

        $redirectUri = $this->generateUrl('vmeste_saas');

        if ($request->isMethod('POST')) {

            $notificationEmail = $request->request->get('notification_email');
            $senderName = $request->request->get('sender_name');
            $senderEmail = $request->request->get('sender_email');
            $csvSeparator = $request->request->get('csv_separator');

            $notificationEmailConstraint = new Email();
            $notificationEmailConstraint->message = "The email " . $notificationEmail . ' is not a valid email';
            $notificationEmailErrorList = $this->get('validator')->validateValue($notificationEmail, $notificationEmailConstraint);

            $senderNameConstraint = new Length(array('min' => 2, 'max' => 255));
            $senderNameEmailErrorList = $this->get('validator')->validateValue($senderName, $senderNameConstraint);

            $senderEmailConstraint = new Email();
            $senderEmailConstraint->message = "The email " . $senderEmail . ' is not a valid email';
            $senderEmailErrorList = $this->get('validator')->validateValue($senderEmail, $senderEmailConstraint);

            $availableSeparators = array(';', ',', 'tab');

            $userId = $this->getRequest()->get('userId', null);

            if (count($notificationEmailErrorList) == 0 && count($senderNameEmailErrorList) == 0
                && count($senderEmailErrorList) == 0 && in_array($csvSeparator, $availableSeparators)
            ) {

                $em = $this->getDoctrine()->getManager();

                if (!($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                    $currentUser = $this->get('security.context')->getToken()->getUser();
                    $userId = $currentUser->getId();
                }

                $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $userId));

                $settingsCollection = $user->getSettings();
                $userSettings = $settingsCollection[0];

                $userSettings->setNotificationEmail($notificationEmail);
                $userSettings->setSenderName($senderName);
                $userSettings->setSenderEmail($senderEmail);
                $userSettings->setCsvColumnSeparator($csvSeparator);

                $em->persist($userSettings);
                $em->flush();

                $redirectUri = $this->redirect($this->generateUrl('customer_settings'));

                if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                    $redirectUri = $this->generateUrl('admin_customer_settings', array('userId' => $userId));
                }

            } else {

                $errorList = "<ul>";
                $errorList .= count($notificationEmailErrorList) > 0 ? "<li>" . $notificationEmailErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= count($senderNameEmailErrorList) > 0 ? "<li>" . $senderNameEmailErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= count($senderEmailErrorList) > 0 ? "<li>" . $senderEmailErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= !in_array($csvSeparator, $availableSeparators) ? "<li>Separator, you have choosen is forbidden.</li>" : "";
                $errorList .= "</ul>";


                $redirectUri = $this->generateUrl('customer_settings', array("email_setting_errors" => $errorList));

                if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                    $redirectUri = $this->generateUrl('admin_customer_settings', array('userId' => $userId, "email_setting_errors" => $errorList));
                }
            }
        }

        return $this->redirect($redirectUri);
    }

    public function updateYkSettingsAction()
    {

        $request = $this->getRequest();

        $redirectUri = $this->generateUrl('vmeste_saas');

        if ($request->isMethod('POST')) {

            $shopid = $request->request->get('yandex_shopid', "");
            $scid = $request->request->get('yandex_scid', "");
            $shoppw = $request->request->get('yandex_shoppw', "");

            $pc = $this->convertCheckboxDataToInt($request->request->get('yandex_pt_pc'));
            $ac = $this->convertCheckboxDataToInt($request->request->get('yandex_pt_ac'));
            $wm = $this->convertCheckboxDataToInt($request->request->get('yandex_pt_wm'));
            $mc = $this->convertCheckboxDataToInt($request->request->get('yandex_pt_mc'));
            $gp = $this->convertCheckboxDataToInt($request->request->get('yandex_pt_gp'));
            $sandbox = $this->convertCheckboxDataToInt($request->get('yandex_sandbox'));

            $em = $this->getDoctrine()->getManager();

            $currentUser = $this->get('security.context')->getToken()->getUser();
            $userId = $currentUser->getId();
            if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                $userId = $this->getRequest()->get('userId', null);
            }

            $user = $em->getRepository('Vmeste\SaasBundle\Entity\User')->findOneBy(array('id' => $userId));

            $settingsCollection = $user->getSettings();
            $userSettings = $settingsCollection[0];

            $yandexKassa = $userSettings->getYandexKassa();

            $yandexKassa->setShopId($shopid);
            $yandexKassa->setScid($scid);
            $yandexKassa->setShoppw($shoppw);
            $yandexKassa->setPc($pc);
            $yandexKassa->setAc($ac);
            $yandexKassa->setWm($wm);
            $yandexKassa->setMc($mc);
            $yandexKassa->setGp($gp);
            $yandexKassa->setSandbox($sandbox);


            $em->persist($yandexKassa);
            $em->flush();

            $redirectUri = $this->redirect($this->generateUrl('customer_settings'));

            if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                $redirectUri = $this->generateUrl('admin_customer_settings', array('userId' => $userId));
            }

        }

        return $this->redirect($redirectUri);
    }

    private function convertCheckboxDataToInt($data)
    {
        return $data == 'on' ? 1 : 0;
    }

    public function updatePasswordAction()
    {

        $errorList = "";

        $logger = $this->get('logger');

        $request = $this->get('request');

        $redirectUri = $this->generateUrl('vmeste_saas');

        if ($request->isMethod('POST')) {


            $notBlank = new NotBlank();
            $length = new Length(array('min' => 6, 'max' => 128));

            $validator = $this->get('validator');

            if (!($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                $oldPassword = $request->request->get('old_password');
                $notBlank->message = "Old password is empty!";
                $oldPasswordErrorList = $validator->validateValue($oldPassword, array($notBlank, $length));
            } else {
                $oldPasswordErrorList = array();
            }

            $password = $request->request->get('new_password');
            $notBlank->message = "Password is empty!";
            $passwordErrorList = $validator->validateValue($password, array($notBlank, $length));

            $passwordRepeat = $request->request->get('new_password_repeat');
            $notBlank->message = "You haven't repeated your password!";
            $passwordRepeatErrorList = $validator->validateValue($passwordRepeat, array($notBlank, $length));

            $passwordsEqual = ($password === $passwordRepeat);

            $userId = $this->getRequest()->get('userId', null);
            $searchParamsArray = array('id' => $userId);

            if (count($oldPasswordErrorList) == 0 && count($passwordErrorList) == 0
                && count($passwordRepeatErrorList) == 0 && $passwordsEqual
            ) {

                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                if (!($this->get('security.context')->isGranted('ROLE_ADMIN'))) {

                    $factory = $this->get('security.encoder_factory');
                    $user = new User();
                    $encoder = $factory->getEncoder($user);
                    $oldPassword = $encoder->encodePassword($oldPassword, $user->getSalt());

                    $currentUser = $this->get('security.context')->getToken()->getUser();
                    $userId = $currentUser->getId();
                    $searchParamsArray = array('id' => $userId, 'password' => $oldPassword);
                }

                $user = $this->getDoctrine()
                    ->getRepository('Vmeste\SaasBundle\Entity\User')
                    ->findOneBy($searchParamsArray);

                if ($user != null) {

                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($password, $user->getSalt());
                    $user->setPassword($password);

                    $em->persist($user);
                    $em->flush();

                    $logger->info('[CHANGE_PASSWORD] For user with id: ' . $userId);

                    $redirectUri = $this->redirect($this->generateUrl('customer_settings'));

                    if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                        $redirectUri = $this->generateUrl('admin_customer_settings', array('userId' => $userId));
                    }
                } else {
                    $userNotFoundErrorMessage = "User not found!";
                    $errorList .= "<li>" . $userNotFoundErrorMessage . "</li>";
                    $redirectUri = $this->generateUrl('customer_settings', array("password_setting_errors" => $errorList));
                }

            } else {
                $errorList .= count($oldPasswordErrorList) > 0 ? "<li>" . $oldPasswordErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= count($passwordErrorList) > 0 ? "<li>" . $passwordErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= count($passwordRepeatErrorList) > 0 ? "<li>" . $passwordRepeatErrorList[0]->getMessage() . "</li>" : "";
                $errorList .= !$passwordsEqual ? "<li>" . "Passwords are not equal!" . "</li>" : "";

                $errorList = "<ul>" . $errorList . "</ul>";
                $redirectUri = $this->generateUrl('customer_settings', array("password_setting_errors" => $errorList));

                if (($this->get('security.context')->isGranted('ROLE_ADMIN'))) {
                    $redirectUri = $this->generateUrl('admin_customer_settings', array('userId' => $userId, "password_setting_errors" => $errorList));
                }
            }
        }

        return $this->redirect($redirectUri);

    }
}