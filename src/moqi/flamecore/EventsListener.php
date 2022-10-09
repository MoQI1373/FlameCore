<?php

namespace moqi\flamecore;

use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\sound\BlazeShootSound;

class EventsListener implements Listener{

    function __construct(Loader $plugin){
        $this->plugin = $plugin;
        $this->server = Server::getInstance();
    }

    function onLogin(PlayerLoginEvent $event){
        $this->plugin->getScheduler()->scheduleRepeatingTask(new ScoreboardTask($this->plugin), 20);
    }

    function onJoin(PlayerJoinEvent $event){
        $p = $event->getPlayer();
        if ($this->plugin->config->get("join-title") != ""){
            $p->sendTitle($this->plugin->config->get("join-title"), 5, 8, 5);
        }

        $pk = ToastRequestPacket::create($this->plugin->config->get("notification-title"), $this->plugin->config->get("notification-description"));
        $p->getNetworkSession()->sendDataPacket($pk);

        $item = new ItemManager($p);
        $item->sendItem();

        $this->plugin->addBossbar($p);

        $p->setGamemode(GameMode::ADVENTURE());

        $event->setJoinMessage('');

        $p->teleport($this->server->getWorldManager()->getWorldByName("hub-world-name")->getSafeSpawn()->add(0.5, 1.5, 0.5));
    }

    function onQuit(PlayerQuitEvent $event){
        $event->setQuitMessage('');
    }

    function onItemUse(PlayerItemUseEvent $event){
        $item = $event->getItem();
        $p = $event->getPlayer();

        if ($p->getWorld() !== $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
            return;
        }

        switch ($item->getId()){
            case VanillaItems::BLAZE_ROD()->getId():
                $this->server->dispatchCommand($p, "report");
                break;
            case VanillaItems::FEATHER()->getId():
                $distance = 8;

                $motFlat = $p->getDirectionPlane()->normalize()->multiply($distance * 3.75 / 20);
                $mot = new Vector3($motFlat->x, 0.5, $motFlat->y);
                $p->setMotion($mot);

                $p->getWorld()->addSound($p->getLocation()->asVector3(), new BlazeShootSound());
                break;
            case VanillaItems::COMPASS()->getId():
                $form = new SimpleForm($this->plugin->config->get("teleporter-form-name"));

                $form->addButton(new Button($this->plugin->config->get("teleporter-form-button1"), null, function (Player $player){
                    $this->server->dispatchCommand($player, $this->plugin->config->get("teleporter-form-command1"));
                }));
                $form->addButton(new Button($this->plugin->config->get("teleporter-form-button2"), null, function (Player $player){
                    $this->server->dispatchCommand($player, $this->plugin->config->get("teleporter-form-command2"));
                }));
                $form->addButton(new Button($this->plugin->config->get("teleporter-form-button3"), null, function (Player $player){
                    $this->server->dispatchCommand($player, $this->plugin->config->get("teleporter-form-command3"));
                }));

                $p->sendForm($form);
                break;
            case VanillaItems::PAPER()->getId():
                $form = new SimpleForm("Information");
                $form->setHeaderText($this->plugin->config->get("information-text"));

                $form->addButton(new Button("Close", null, function (Player $player){

                }));
                break;
            case VanillaBlocks::BARRIER()->asItem()->getId():
                $p->kick($this->plugin->config->get("leave-message"));
                break;
        }
    }

    function onBreakBlock(BlockBreakEvent $event){
        $p = $event->getPlayer();

        if (!$p->hasPermission("flamecore.op")){
            if ($p->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
                $event->cancel();
            }
        }
    }

    function onPlaceBlock(BlockPlaceEvent $event){
        $p = $event->getPlayer();

        if (!$p->hasPermission("flamecore.op")){
            if ($p->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
                $event->cancel();
            }
        }
    }

    function onDamage(EntityDamageEvent $event){
        $entity = $event->getEntity();

        if ($entity->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
            $event->cancel();
        }
    }

    function onDropItem(PlayerDropItemEvent $event){
        $p = $event->getPlayer();

        if (!$p->hasPermission("flamecore.op")){
            if ($p->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
                $event->cancel();
            }
        }
    }

    function onExhaust(PlayerExhaustEvent $event){
        $p = $event->getPlayer();

        if ($p->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
            $event->cancel();
        }
    }

    function onMove(PlayerMoveEvent $event){
        $p = $event->getPlayer();

        if ($p->getWorld() === $this->server->getWorldManager()->getWorldByName($this->plugin->config->get("hub-world-name"))){
            if ($p->getLocation()->y < -5){
                $p->teleport($this->server->getWorldManager()->getWorldByName("hub-world-name")->getSafeSpawn()->add(0.5, 1.5, 0.5));
            }
        }
    }

    function onChangeWorld(EntityTeleportEvent $event){
        $p = $event->getEntity();
        if (!$p instanceof Player) return;

        if ($event->getTo()->getWorld() != $event->getFrom()->getWorld()){
            if ($event->getTo()->getWorld() == $this->server->getWorldManager()->getWorldByName("hub-world-name")){
                $this->plugin->addBossbar($p);
            } else{
                $this->plugin->removeBossbar($p);
            }
        }
    }
}
