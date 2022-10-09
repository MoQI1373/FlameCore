<?php

namespace moqi\flamecore\commands;

use moqi\flamecore\ItemManager;
use moqi\flamecore\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;

class HubCommands extends Command{

    function __construct(Loader $plugin)
    {
        parent::__construct("hub", "Return to hub");
        $this->plugin = $plugin;
    }

    function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;

        $sender->setGamemode(GameMode::ADVENTURE());

        $item = new ItemManager($sender);
        $item->sendItem();

        $this->plugin->addBossbar($sender);
        $sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName("hub-world-name")->getSafeSpawn());
    }
}
