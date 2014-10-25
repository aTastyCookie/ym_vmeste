<?php
namespace Vmeste\SaasBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vmeste\SaasBundle\Util\Rebilling;

/**
 * To launch use: $ app/console vmeste:recurrent
 * Class RecurrentCommand
 * @package Vmeste\SaasBundle\Command
 */
class RecurrentCommand extends ContainerAwareCommand
{
    const ACTIVE = 1;

    const NON_ACTIVE = 0;

    protected function configure()
    {
        $this
            ->setName('vmeste:recurrent')
            ->setDescription('Notify and run recurrents');
        /*->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
        ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');*/
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        $container = new Adapter($this->getContainer());
        //$context = $container->get('templating');
        $context_mailer = $container->get('mailer');
        $params = array(
            'ymurl' =>  $this->getContainer()->getParameter('recurrent.ymurl'),
            'icpdo' => $em,
            'apphost' =>  $this->getContainer()->getParameter('recurrent.apphost'),
            'context' => $container,
            'context_mailer' => $context_mailer
        );
        $recurrent = new Rebilling($params);
        $recurrent->notify();
        $recurrent->run();
        echo("Finished" . "\n\n");
    }
}

class Adapter {

    private $_container;

    function __construct($container) {
        $this->_container = $container;
    }

    function renderView($template, array $params) {
        return $this->_container->get('templating')->render($template, $params);
    }

    function get($service) {
        return $this->_container->get($service);
    }
}