<?php

namespace App\Libraries;

class BackgroundProcess
{
    const OS_WINDOWS = 1;
    const OS_NIX = 2;
    const OS_OTHER = 3;
    protected int $serverOS;
    private ?string $command;
    private int $pid;

    public function __construct(?string $command = null)
    {
        $this->command = $command;
        $this->serverOS = $this->getOS();
    }

    /**
     * Get OS
     */
    protected function getOS(): int
    {
        $os = strtoupper(PHP_OS);
        if (substr($os, 0, 3) === 'WIN') {
            return self::OS_WINDOWS;
        } else if ($os === 'LINUX' || $os === 'FREEBSD' || $os === 'DARWIN') {
            return self::OS_NIX;
        }
        return self::OS_OTHER;
    }

    /**
     * Create From PID
     */
    static public function createFromPID(int $pid): BackgroundProcess
    {
        $process = new self();
        $process->setPid($pid);
        return $process;
    }

    /**
     * Runs the command in a background process.
     */
    public function run(string $outputFile = '/dev/null', bool $append = false)
    {
        if ($this->command === null) {
            return;
        }

        switch ($this->getOS()) {
            case self::OS_WINDOWS:
                shell_exec(sprintf('%s &', $this->command));
                break;
            case self::OS_NIX:
                $this->pid = (int)shell_exec(sprintf('%s %s %s 2>&1 & echo $!', $this->command, ($append) ? '>>' : '>', $outputFile));
                break;
            default:
                throw new RuntimeException(sprintf(
                    'Could not execute command "%s" because operating system "%s" is not supported by',
                    $this->command, PHP_OS
                ));
        }
    }

    /**
     * Returns if the process is currently running.
     */
    public function isRunning(): bool
    {
        $this->checkSupportingOS('BackgroundProcess can only check if a process is running on *nix-based ' .
            'systems, such as Unix, Linux or Mac OS X. You are running "%s".');
        try {
            $result = shell_exec(sprintf('ps %d 2>&1', $this->pid));
            if (count(preg_split("/\n/", $result)) > 2 && !preg_match('/ERROR: Process ID out of range/', $result)) {
                return true;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * Check OS
     */
    protected function checkSupportingOS(string $message)
    {
        if ($this->getOS() !== self::OS_NIX) {
            throw new RuntimeException(sprintf($message, PHP_OS));
        }
    }

    /**
     * Stops the process.
     */
    public function stop(): bool
    {
        $this->checkSupportingOS('BackgroundProcess can only stop a process on *nix-based systems, such as ' .
            'Unix, Linux or Mac OS X. You are running "%s".');
        try {
            $result = shell_exec(sprintf('kill %d 2>&1', $this->pid));
            if (!preg_match('/No such process/', $result)) {
                return true;
            }
        } catch (Exception $e) {
        }
        return false;
    }

    /**
     * Returns the ID of the process.
     */
    public function getPid(): int
    {
        $this->checkSupportingOS('BackgroundProcess can only return the PID of a process on *nix-based systems, ' .
            'such as Unix, Linux or Mac OS X. You are running "%s".');
        return $this->pid;
    }

    /**
     * Set the process id.
     */
    protected function setPid(int $pid)
    {
        $this->pid = $pid;
    }
}
