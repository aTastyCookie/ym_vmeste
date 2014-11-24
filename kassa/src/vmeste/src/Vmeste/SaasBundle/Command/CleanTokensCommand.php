<?php
/**
 * Created by PhpStorm.
 * Authors: Eugene Avrukevich <eugene.avrukevich@gmail.com>
 * Date: 10/3/14
 * Time: 12:13 AM
 */

namespace Vmeste\SaasBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * To launch use: $ app/console vmeste:cleantokens
 * Class CleanTokensCommand
 * @package Vmeste\SaasBundle\Command
 */
class CleanTokensCommand extends ContainerAwareCommand
{
    const ACTIVE = 1;

    const NON_ACTIVE = 0;

    protected function configure()
    {
        $this
            ->setName('vmeste:cleantokens')
            ->setDescription('Clean recover tokens');
        /*->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
        ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');*/
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $recoverTimeLimit = time() - 3 * 3600;

        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();

        $qb = $em->createQueryBuilder();
        $qb->select('rt')
            ->from('Vmeste\SaasBundle\Entity\RecoverToken', 'rt')
            ->where('rt.created < :date')
            ->andWhere('rt.active = :active')
            ->setParameters(array('date' => $recoverTimeLimit, 'active' => self::ACTIVE));

        $tokens = $qb->getQuery()->getResult();

        foreach ($tokens as $token) {
            $token->setActive(self::NON_ACTIVE);
            $em->persist($token);
        }

        $em->flush();

        //echo("\n\nRecover tokens affected: " . count($tokens) . "\n");
        //echo("Finished" . "\n\n");
    }
} 