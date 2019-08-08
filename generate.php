<?php

$ph = new Phar('./M2P.phar', 0);

$ph->setStub(<<<'EOS'
<?php
__HALT_COMPILER();
EOS
);

$ph->addFile('./plugin.yml', 'plugin.yml');
$ph->addFile('./src/MIDI2PMMP/Main.php', 'src/MIDI2PMMP/Main.php');
$ph->addFile('./src/MIDI2PMMP/commands/M2PCommand.php', 'src/MIDI2PMMP/commands/M2PCommand.php');
