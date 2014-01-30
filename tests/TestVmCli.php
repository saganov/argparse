<?php

require __DIR__.'/../src/Argparse.php';

$vmCli = new Argparse('VM', 'The tool to work with virtual machines.');

/**
 * VM Commands
 */
$command = $vmCli->addSubparsers('command',
                                 'COMMANDS',
                                 'Run `vm <command> --help` to get detail help ',
                                 'Main command of the VM tool',
                                 'VM ');
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
                               'Install ETAdirect specified version to VM');
$install->addArgument('version', array('help' => 'Version of ETAdirect release, like v42_12.m0137478, or v42_12 or v4.2.12'));
$install->addArgument('package', array('required' => false,
                                       'help'     => 'Name of the package: be, bs, fe, is. By default installs all.'));

/**
 * INSTANCE Commands
 */
$subcommand = $command->addSubparsers('instance',
                                      'SUBCOMMAND',
                                      'Run `vm instance <command> --help` to get detail help ',
                                      'Commands to deal with instance',
                                      'VM INSTANCE ');
// INIT
$init = $subcommand->addParser('init',
                               'INIT',
                               'Init the instance on the VM');
$init->addArgument('name', array('required' => false,
                                 'default'  => 'vm353',
                                 'help'     => 'Company name. Instance name will be the value of config option "vm_hostname" without .ua3 dot and specified company name. Dtabase name will be the same as the specified company name. For example, if name=cox, and vm_hostname=vm353.ua3, instance name be "vm353.cox" and database "cox". If the name is not specified, the only value of config option "vm_hostname" without .ua3 is used as instnce name.'));
$init->addArgument('version', array('required' => false,
                                    'help'     => 'Instance version (for ex.: 4.5.9.131206). If the version is not specified, the default version from be.conf is used.'));
$init->addArgument('dump', array('required' => false,
                                 'help'     => 'Full path to SQL dump. If the dump is not specified, the only empty_db is used.'));
// DB
$db = $subcommand->addParser('db',
                             'DB',
                             'Update instance DB');

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