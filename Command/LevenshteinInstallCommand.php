<?php

/*
    Copyright 2014 Jessym Reziga https://github.com/jreziga/JrkLevenshteinBundle

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace Jrk\LevenshteinBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Jrk\LevenshteinBundle\ORM\Doctrine\DQL\LevenshteinRatioFunction;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jrk\LevenshteinBundle\ORM\Doctrine\DQL\LevenshteinFunction;


class LevenshteinInstallCommand extends ContainerAwareCommand
{
     /** @var string $defaultName */
    protected static $defaultName = 'jrk:levenshtein:install'; // Make command lazy load

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Create LEVENSHTEIN() and LEVENSHTEIN_RATIO() functions in mysql.')
            ->setDefinition(array())
            ->setHelp(<<<EOT
The <info>jrk:levenshtein:install</info> command creates the pathfile:

  <info>php app/console jrk:levenshtein:install</info>

Create LEVENSHTEIN() and LEVENSHTEIN_RATIO() functions in mysql.

EOT
            );
    }

   

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db_host = $this->getContainer()->getParameter('database_host');
        $db_user = $this->getContainer()->getParameter('database_user');

        $functions = array(
            LevenshteinFunction::getFunctionName() => LevenshteinFunction::getImportSql($db_user,$db_host),
            LevenshteinRatioFunction::getFunctionName() => LevenshteinRatioFunction::getImportSql($db_user,$db_host)
        );

        foreach($functions as $name => $function) {
            $levenshteinFunction =
            $state = 'OK';

            try {
                $this->getContainer()->get('doctrine')->getManager()->getConnection()->prepare($function)->execute();
            } catch(\Doctrine\DBAL\DBALException $e) {
                if ($this->findExceptionCode($e,42000)) {
                    $state = 'ALREADY DEFINED';
                } else {
                    $state = 'ERROR';
                }
            }

            $output->writeln(sprintf("function: %s() \t\t[%s]",$name,$state));
        }

    }


    public function findExceptionCode($exception, $baseCode) {

        while($exception != null) {
            $code = $exception->getCode();
            if ($code == $baseCode) {
                return true;
            }
            $exception = $exception->getPrevious();
        }

        return false;
    }


}