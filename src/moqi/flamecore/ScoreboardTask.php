<?php

namespace moqi\flamecore;

use pocketmine\scheduler\Task;
use onebone\economyapi\EconomyAPI;

class ScoreboardTask extends Task{

    function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p){
            if ($p->getWorld() === $this->plugin->getServer()->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
                $scoreboard = $this->plugin->scoreboard;

                $scoreboard->new($p, 'lobby', "§bLobby");

                $lines = [
                    1 => "§r",
                    2 => "§eCoins: §a" . EconomyAPI::getInstance()->myMoney($p),
                    3 => "§eName: §a" . $p->getName(),
                    4 => "§r ",
                    5 => "§eRank: §a" . $this->plugin->getRank($p),
                    6 => "§r  ",
                    7 => "§ePing: §a" . $p->getNetworkSession()->getPing(),
                    8 => "§ePlayers: §a" . count($this->plugin->getServer()->getOnlinePlayers()),
                    9 => "§r   ",
                    10 => "§e" . $this->plugin->config->get("server-ip")
                ];

                foreach ($lines as $line => $text){
                    $scoreboard->setLine($p, $line, $text);
                }
            }
        }
    }
}
