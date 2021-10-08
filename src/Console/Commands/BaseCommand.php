<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\{ConfirmationQuestion, Question};
use Yoke\Servers\Manager;

/**
 * Class BaseCommand.
 *
 * Abstract base command to help to build neat commands.
 */
abstract class BaseCommand extends Command
{
    protected string $name;
    protected string $description;
    protected array $arguments = [];
    /** @var Manager Servers manager instance. */
    protected Manager $manager;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected QuestionHelper $questionHelper;

    /**
     * Main Command method, calls the fire command on its child commands.
     *
     * @param InputInterface $input Application provided input handler.
     * @param OutputInterface $output Application provided output handler.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Assign input and output streams.
        $this->input = $input;
        $this->output = $output;
        // Assign the QuestionHelper instance.
        $this->questionHelper = $this->getHelper('question');
        // Created and assign a new Servers Manager instance.
        $this->manager = new Manager();

        // Call the child command fire() method.
        $this->fire($input);

        return self::SUCCESS;
    }

    /**
     * Initialize the command for the console Application.
     */
    protected function configure(): void
    {
        // Configure the command name.
        $this->setName($this->name);
        // Configure the command description.
        $this->setDescription($this->description);

        $alias = strtolower($this->name[0]);

        // Configure single letter alias for the command.
        $this->setAliases([$alias]);

        // Loop through arguments and register them.
        foreach ($this->arguments as $argument) {
            $this->addArgument($argument[0], $argument[1], $argument[2]);
        }
    }

    /**
     * Abstract fire method to be implemented on child commands.
     *
     * @param InputInterface $input
     */
    abstract protected function fire(InputInterface $input): void;

    /**
     * Abstracts the question process into a single method.
     * Some options are defined by convention, like formatting.
     *
     * @param string $question The question being asked.
     * @param null $default Default value in case the user does not provide an answer.
     *
     * @return string The user input or the default value.
     */
    protected function ask(string $question, $default = null): string
    {
        // Creates a new question instance, using the convention formatting.
        $askQuestion = new Question($this->format($question, 'question'), $default);

        // Do ask que question created and return its answer value.
        return $this->questionHelper->ask($this->input, $this->output, $askQuestion);
    }

    /**
     * Asks a Confirmation (Yes/No) Question.
     *
     * Inputs like Y, Yes will make this method return true.
     * Any other input will return false.
     *
     * @param string $question The question to be confirmed.
     *
     * @return bool Confirmed or Not.
     */
    protected function askConfirmation(string $question): bool
    {
        // Creates a new confirmation instance using convention formatting.
        $confirmQuestion = new ConfirmationQuestion($this->format("{$question} (Y/n)", 'question'), false);

        // Ask and return the input.
        return $this->questionHelper->ask($this->input, $this->output, $confirmQuestion);
    }

    /**
     * Format a given string into a colored output format.
     *
     * @param string $text The string to be formatted.
     * @param string $type Desired coloring type.
     *
     * @return string The formatted string.
     */
    protected function format(string $text, string $type = 'info'): string
    {
        return "\n<{$type}>{$text}</{$type}> ";
    }

    /**
     * Write a string into the console output.
     *
     * @param string $text The string to be displayed.
     * @param string $format The coloring format.
     */
    protected function writeln(string $text, string $format = 'info'): void
    {
        // Uses output handler to write the formatted string.
        $this->output->writeln($this->format($text, $format));
    }

    /**
     * Write a not formatted string into the console output.
     *
     * @param string $text The string to be displayed.
     */
    protected function writelnPlain(string $text): void
    {
        // Uses output handler to write the formatted string.
        $this->output->writeln($text);
    }

    /**
     * Write a question formatted string into the console.
     *
     * @param string $text The string to be displayed.
     */
    protected function question(string $text): void
    {
        $this->writeln($text, 'question');
    }

    /**
     * Write a information formatted string into the console.
     *
     * @param string $text The string to be displayed.
     */
    protected function info(string $text): void
    {
        $this->writeln($text);
    }

    /**
     * Write a comment formatted string into the console.
     *
     * @param string $text The string to be displayed.
     */
    protected function comment(string $text): void
    {
        $this->writeln($text, 'comment');
    }

    /**
     * Write a error formatted string into the console.
     *
     * @param string $text The string to be displayed.
     */
    protected function error(string $text): void
    {
        $this->writeln($text, 'error');
    }

    /**
     * Gets the provided value to a given command argument.
     *
     * @param string $name Argument's name
     *
     * @return mixed The user provided value.
     */
    protected function argument(string $name)
    {
        return $this->input->getArgument($name);
    }
}
