<?php
namespace Vmeste\SaasBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vmeste\SaasBundle\Util\Rebilling;
use Vmeste\SaasBundle\Util\RebillingTest;

/**
 * To launch use: $ app/console vmeste:recurrent
 * Class RecurrentCommand
 * @package Vmeste\SaasBundle\Command
 */
class RecurrentCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('vmeste:recurrent')
            ->setDescription('Notify and run recurrents')
            ->addArgument('test', InputArgument::OPTIONAL);
        /*->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters');*/
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
            'context' => $this->getContainer(),
            'context_adapter' => $container,
            'context_mailer' => $context_mailer
        );
        $recurrent = new Rebilling($params);

        $test = $input->getArgument('test');
        if($test && $test == 'testrun') {
            $recurrent = new RebillingTest($params);
            $recurrent->recurrent_test();
        } else {
            $recurrent->notify();
            $recurrent->run();
        }

        //echo("Finished" . "\n\n");
        exit;
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