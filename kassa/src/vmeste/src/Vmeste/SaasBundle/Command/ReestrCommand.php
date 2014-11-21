<?php
namespace Vmeste\SaasBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query;

/**
 * To launch use: $ app/console vmeste:reestr
 * Class ReestrCommand
 * @package Vmeste\SaasBundle\Command
 */
class ReestrCommand extends ContainerAwareCommand
{
    const DAY = 86400;

    protected function configure()
    {
        $this
            ->setName('vmeste:reestr')
            ->setDescription('Send daily reestr');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $today_d = date("j");
        $today_m = date("n");
        $today_y = date("Y");
        $yesterday_start = mktime(0, 0, 0, $today_m, $today_d, $today_y) - self::DAY;
        $yesterday_end = mktime(23, 59, 59, $today_m, $today_d, $today_y) - self::DAY;
        $container = $this->getContainer();

        $emailFrom = $container->getParameter('reestr.email.from');
        $emailTo = $container->getParameter('reestr.email.to');

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder->select('r.invoice_id, r.amount, r.last_operation_time,
        r.subscription_date, r.last_status, r.last_error, r.pan, r.next_date, d.name, d.email, d.details, c.subTitle')
            ->from('Vmeste\SaasBundle\Entity\Recurrent', 'r')
            ->leftJoin('Vmeste\SaasBundle\Entity\Donor', 'd', 'WITH', ' r.donor = d')
            ->leftJoin('Vmeste\SaasBundle\Entity\Campaign', 'c', 'WITH', 'r.campaign = c')
            ->where('r.last_operation_time >= :yesterdayStart')
            ->andWhere('r.last_operation_time <= :yesterdayEnd')
            ->setParameter('yesterdayStart', $yesterday_start)
            ->setParameter('yesterdayEnd', $yesterday_end)
            ->orderBy('r.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $data = $query->getResult(Query::HYDRATE_ARRAY);

        $mailer = $this->getContainer()->get('mailer');
        $date_file = date("j-n-Y", $yesterday_start);
        $message = \Swift_Message::newInstance();
        $message->setSubject("РЕЕСТР ПЛАТЕЖЕЙ В приложении Касса для благотворительности от $date_file")
                ->setFrom($emailFrom)
                ->setTo($emailTo);

        if(count($data)>0) {
            $can_send = false;

            $path = $this->getReestrPath($date_file);
            $reestrDir = $this->getReestrDir();

            if(!file_exists ($reestrDir)) {
                // Try to create the directory
                $mkdirResult = mkdir($reestrDir, 0700);
                if(!$mkdirResult) {
                    $message->setBody("Директорию $reestrDir невозможно создать");
                } else {
                    $can_send = true;
                }
            } elseif(!is_writable($reestrDir)) {
                $message->setBody("Yевозможно сохранить файл в директорию $reestrDir");
            } else {
                $can_send = true;
            }

            if($can_send) {
                $fp = fopen($path, 'w');
                fwrite($fp, "\xEF\xBB\xBF" ) ;
                $titleRow = array('invoiceId', 'amount', 'Operation', 'Subscription Date', 'Status', 'Error',
                    'pan', 'Next Date', 'Donor Name', 'Donor Email', 'Donor Details', 'Campaign');
                fputcsv($fp, $titleRow, ";");

                foreach ($data as $fields) {
                    $fieldsToPut = $fields;
                    //print_r($fieldsToPut);
                    $fieldsToPut["last_operation_time"] = date("j-n-Y", $fieldsToPut["last_operation_time"]);
                    $fieldsToPut["subscription_date"] = date("j-n-Y", $fieldsToPut["subscription_date"]);
                    $fieldsToPut["next_date"] = date("j-n-Y", $fieldsToPut["next_date"]);

                    switch($fieldsToPut["last_error"]) {
                        case 0: $fieldsToPut["last_error"] = "Без ошибок"; break;
                        case 110: $fieldsToPut["last_error"] =
                                "У данного пользователя нет прав на выполнение операции с запрошенными параметрами"; break;
                        case 112: $fieldsToPut["last_error"] = "Неверное значение параметра invoiceId"; break;
                        case 113: $fieldsToPut["last_error"] = "Неверное значение параметра shopId"; break;
                        case 114: $fieldsToPut["last_error"] = "Неверное значение параметра orderNumber"; break;
                        case 115: $fieldsToPut["last_error"] = "Неверное значение параметра clientOrderId"; break;
                        case 402: $fieldsToPut["last_error"] = "Неверное значение параметра amount"; break;
                        case 405: $fieldsToPut["last_error"] = "Неуникальный номер операции"; break;
                        case 415: $fieldsToPut["last_error"] = "Заказ с указанным номером транзакции (invoiceId) отсутствует"; break;
                        case 601: $fieldsToPut["last_error"] = "Запрещено повторять карточные платежи в магазин"; break;
                        case 602: $fieldsToPut["last_error"] = "Повтор данного платежа запрещён"; break;
                        case 603: $fieldsToPut["last_error"] = "Для данного платежа обязателен orderNumber"; break;
                        case 604: $fieldsToPut["last_error"] = "Неверное значение параметра cvv"; break;
                        case 1000: $fieldsToPut["last_error"] = "Техническая ошибка"; break;
                        default: $fieldsToPut["last_error"] = "Неизвестная ошибка"; break;
                    }

                    fputcsv($fp, $fieldsToPut, ";");
                }
                fclose($fp);
                $message->setBody("Смотрите вложение")
                        ->attach(\Swift_Attachment::fromPath($path));
            }

        } else {
            $message->setBody("Сегодня платежей не было");
        }
        $mailer->send($message);
        //echo "\n"."count data: ".count($data)."\n";
        //echo "sending..."."\n";
        //echo $message->getBody()."\n";

        exit;
    }

    protected function getReestrPath($date_file)
    {
        return $this->getReestrDir() . $date_file . '.csv';
    }

    protected function getReestrDir()
    {
        return __DIR__ . '/../../../../data/reestr/';
    }
}