<?php

declare(strict_types=1);

namespace moqi\flamecore\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;

class Scoreboard {

    /** @var array $scoreboards */
    private $scoreboards = [];


    /**
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     */
    public function new(Player $player, string $objectiveName, string $displayName): void{
        if(isset($this->scoreboards[$player->getName()])){
            $this->remove($player);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $player->getName();
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        $this->scoreboards[$player->getName()] = $objectiveName;
    }

    /**
     * @param Player $player
     */
    public function remove(Player $player): void{
        $objectiveName = self::getObjectiveName($player);
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $player->getName();
        $player->getNetworkSession()->sendDataPacket($pk);
        unset($this->scoreboards[$objectiveName]);
    }

    /**
     * @param Player $player
     * @param int $score
     * @param string $message
     */
    public function setLine(Player $player, int $score, string $message): void{
        if(!isset($this->scoreboards[$player->getName()])){
            return;
        }
        if($score > 15 || $score < 0){
            error_log("Score must be between the value of 1-15. $score out of range");
            return;
        }
        $objectiveName = self::getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $player->getName();
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function getObjectiveName(Player $player): ?string{
        return isset($this->scoreboards[$player->getName()]) ? $this->scoreboards[$player->getName()] : null;
    }
}
