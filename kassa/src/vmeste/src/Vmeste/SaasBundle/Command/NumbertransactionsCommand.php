<?php

namespace Vmeste\SaasBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vmeste\SaasBundle\Entity\Donor;
use Vmeste\SaasBundle\Entity\Recurrent;
use Vmeste\SaasBundle\Util\Clear;

/**
 * To launch use: $ app/console vmeste:numbertransactions
 * Class NumbertransactionsCommand
 * @package Vmeste\SaasBundle\Command
 */
class NumbertransactionsCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('vmeste:numbertransactions')
            ->setDescription('Update transactions amounts');
        /*->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
        ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');*/
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select('t')
            ->from('Vmeste\SaasBundle\Entity\Transaction', 't');

        $transactions = $qb->getQuery()->getResult();

        foreach ($transactions as $transaction) {
            $details = $transaction->getDetails();
            $gross = $transaction->getGross();
            $details = explode("orderSumAmount=", $details);
            if(is_array($details) && isset($details[1])) {
                $details = explode("&", $details[1]);
                if(is_array($details) && isset($details[0])) {
                    $orderSumAmount = Clear::number(number_format((float)stripslashes($details[0]), 2, '.', ''));
                    if($orderSumAmount != $gross) {
                        $transaction->setGross($orderSumAmount);
                        $em->persist($transaction);
                        $baseInvoice = $transaction->getInvoiceId();
                        $existingRecurrent = $em->getRepository('Vmeste\SaasBundle\Entity\Recurrent')->findOneBy(
                            array('invoice_id' => $baseInvoice));

                        $donor = $transaction->getDonor();
                        if($donor) {
                            $donor->setAmount($orderSumAmount);
                            $em->persist($donor);
                        }

                        if($existingRecurrent) {
                            $existingRecurrent->setAmount($orderSumAmount);
                            $em->persist($existingRecurrent);
                        }
                    }
                }
            }
        }
        $em->flush();
    }
} 