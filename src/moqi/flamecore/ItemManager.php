<?php

namespace moqi\flamecore;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class ItemManager{

    function __construct(Player $player){
        $this->player = $player;
    }

    function sendItem(){
        $this->player->getInventory()->clearAll();
        $this->player->getArmorInventory()->clearAll();

        $this->player->getInventory()->setItem(0, VanillaItems::BLAZE_ROD()->setCustomName("§eReports"));
        $this->player->getInventory()->setItem(1, VanillaItems::FEATHER()->setCustomName("§eLeaper"));
        $this->player->getInventory()->setItem(5, VanillaItems::COMPASS()->setCustomName("§eTeleporter"));
        $this->player->getInventory()->setItem(7, VanillaItems::PAPER()->setCustomName("§eInformation"));
        $this->player->getInventory()->setItem(8, VanillaBlocks::BARRIER()->asItem()->setCustomName("§cLeave Server"));
    }
}
