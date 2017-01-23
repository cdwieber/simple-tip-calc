<?php
namespace Chris;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class TipCommand
 * @package Chris
 */
class TipCommand extends Command {

    /**
     * Initial config for Symfony
     */
    public function configure()
    {
        $this->setName('calculate')
            ->setDescription('Calculates a tip amount based on input')
            ->addArgument('amount', InputArgument::OPTIONAL, 'Output a table of suggested tips');
    }

    /**
     * Interactive mode, prompting the user for inputs
     */
    private function runInteractive(InputInterface $input, OutputInterface $output)
    {
        //Print initial message
        $infoMessage = "Hello! This simple application will calculate your tip based on 
                        the amount of your bill. \n";

        $output->writeln("<info>{$infoMessage}</info>");

        //Set up our questions
        $helper = $this->getHelper('question');

        //Amount prompt
        $askAmount = new Question('<question>Please enter the amount of the bill: </question>');

        //Set up validator callback for amount (only amount is required)
        $askAmount->setValidator(function ($answer) {
            if ($answer == '' || !is_numeric($answer)) {
                throw new \RuntimeException('Amount cannot be blank, nor can it be a non-number!');
            }
            return $answer;
        });

        //Tip prompt (defaults to 15)
        $askTip = new Question('<question>Please enter the tip percentage [15]: </question>');

        //Ask our questions
        $amount = $helper->ask($input, $output, $askAmount);
        $setTip = $helper->ask($input, $output, $askTip);

        //Ensure our outputs are only two decimal places
        $tipPercent = is_null($setTip) ? 15 : $setTip;

        $tip = $this->calculate($amount, $tipPercent);

        $resultString = "The proper tip for \$". $amount . " at ". $tipPercent . "% is \$" . $tip;
        $output->writeln("<info>{$resultString}</info>");

        return;
    }


    /**
     * Auto mode, returning some standard tip percentages when just
     * an amount is passed as an argument
     */
    private function runAuto(InputInterface $input, OutputInterface $output, $amount) {

        $output->writeln("<info>Here are some suggested tips for \${$amount}!</info> \n");

        $table = new Table($output);

        // Symfony requires a multidimensional array here in the case of additional rows
        // even though we only need one
            $rowArray = [
                [
                    "\$" . round($amount * .15, 2),
                    "\$" . round($amount * .18, 2),
                    "\$" . round($amount * .2, 2),
                    "\$" . round($amount * .25, 2),
                ]
            ];

        $table->setHeaders(['15%','18%','20%','25%'])
            ->setRows($rowArray);

        $table->render();

        return;

    }

    /**
     * Run some predetermined calcs based on amounts
     * @param $amount
     * @param $tipPercent
     * @return $tip
     */
    private function calculate($amount, $tipPercent)
    {
        //Convert whole-number percentage expression to decimal
        $tipDecimal = $tipPercent / 100;

        //Calculate and round to dollars and sense
        $tip = round(($amount * $tipDecimal), 2);

        return $tip;
    }

    /**
     * Proceed with execution of command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $amount = $input->getArgument('amount');


        //If there are no arguments given, run interactive mode and prompt the user
        if (!$amount) {
            $this->runInteractive($input, $output);
            return;
        }

        if (!is_numeric($amount)) {
            throw new \RuntimeException("Argument must be numeric!");
            exit;
        }

        //Otherwise, we'll run calculations on some predetermined amounts
        $this->runAuto($input, $output, $amount);

        return;
    }
}