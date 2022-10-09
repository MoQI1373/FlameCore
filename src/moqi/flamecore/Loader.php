<?php

namespace moqi\flamecore;

use libs\xenialdan\apibossbar\BossBar;
use moqi\flamecore\commands\HubCommands;
use moqi\flamecore\utils\Scoreboard;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase{

    function onEnable(): void
    {
        $this->getLogger()->info("Enabled");

        $this->saveResource("Config.yml");
        $this->config = new Config($this->getDataFolder() . "Config.yml");

        $this->bossbar = new BossBar();
        $this->pureperms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->scoreboard = new Scoreboard();

        $this->getServer()->getCommandMap()->register('hub', new HubCommands($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventsListener($this), $this);
    }

    function addBossbar(Player $player){
        $name = $this->config->get("bossbar-text");
        $name = str_replace("&", "§", $name);
        $this->bossbar->setTitle($name);
        $this->bossbar->setPercentage(100);
        $this->bossbar->addPlayer($player);
    }

    function removeBossbar(Player $player){
        $this->bossbar->removePlayer($player);
    }

    function getRank(Player $player): string{
        $group = $this->pureperms->getUserDataMgr()->getData($player)['group'];

        if (!is_null($group)){
            return $group;
        } else{
            return "No Rank";
        }
    }
}
