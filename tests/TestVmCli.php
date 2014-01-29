<?php

require __DIR__.'/../src/Argparse.php';

$vmCli = new Argparse('VM', 'The tool to work with virtual machines.');
$command = $vmCli->addSubparsers('COMMANDS', 'Run `vm help <command>` to get detail help ', 'Main command of the VM tool');
// VM Commands
// TUNE
$tune  = $command->addParser('tune',
                             'TUNE',
                             'Tune VM. Set hostname, NFS etc.',
                             function(){print "TUNE has been run\n";});
// SHARE
$share = $command->addParser('share',
                             'SHARE',
                             'Clear VM from personal data and upload a disc image to public dir.',
                             function(){print "SHARE has been run\n";});
// PREPARE-DEV
$prepare = $command->addParser('prepare-dev',
                               'PREPARE-DEV',
                               'Create symlinks on the VM for specified instance.');
$prepare->addArgument('name', array('help' => 'Instance name. If the name is not specified, the value of config option "vm_hostname" without .ua3 is used.'));
// INSTALL
$install = $command->addParser('install',
                               'INSTALL',
                               ' Install ETAdirect specified version to VM');
$install->addArgument('version', array('help' => 'Version of ETAdirect release, like v42_12.m0137478, or v42_12 or v4.2.12'));
$install->addArgument('package', array('required' => false,
                                       'help'     => 'Name of the package: be, bs, fe, is. By default installs all.'));

//$vmCli->help();

$input = array('tune', 'b', '--bazbb', 'BAZBB');
$input = array('tune', 'b', '--help', 'BAZBB');
$input = array('tune');
//$input = array('--help', 'BAZBB');

if(count($_SERVER['argv']) > 1)
{
    printf("INPUT: %s\n", implode(' ', array_slice($_SERVER['argv'], 1)));
    $vmCli->parse();
}
else
{
    printf("INPUT: %s\n", implode(' ', $input));
    $vmCli->parse($input);

}
printf("CONTEXT: ");
var_dump($vmCli->debug('context'));

printf("REMAINDER: ");
var_dump($vmCli->remainder());

?>