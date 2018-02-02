<?php

namespace CreativeControl;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\plugin\Plugin;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;


class CreativeControl extends PluginBase implements Listener{

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info('§2【创造控制中心】插件启动中...');
		@mkdir($this->getDataFolder());
		$this->Config = new Config($this->getDataFolder() . 'Config.yml',Config::YAML,['创造切换提示' => '已为您清空背包','禁止摆放的创造方块' => 'null','被监控的方块' => 'null']);
		$this->Record = new Config($this->getDataFolder() . 'Record.yml',Config::YAML,[]);
	}

	public function PlayerGameModeChange(PlayerGameModeChangeEvent $event){               //模式切换事件
		$player = $event->getPlayer();
		$massage = $this->Config->get('创造切换提示');
		if(!$event->getPlayer()->getGamemode()==0)
			{
  				$player->getInventory()->clearAll();
  				$player->sendMessage('§b［创造控制中心］§a'.$massage);
 		}

	}

	public function onChestOpen(PlayerInteractEvent $event){             //玩家触摸事件
			$player = $event->getPlayer();
			$blockID = $event->getBlock()->getID();
			if(!$event->getPlayer()->getGamemode()==0 && $blockID == 54){
				$event->setCancelled();
				$player->sendMessage('§b［创造控制中心］§a您在创造模式下无法开启箱子');
			}
	}

	public function DropItem(PlayerDropItemEvent $event){            //物品丢弃事件
			$player = $event->getPlayer();
			if(!$event->getPlayer()->getGamemode()==0){
				$event->setCancelled();
				$player->sendMessage('§b［创造控制中心］§a您在创造模式下无法丢弃物品');
			}
	}

	public function Place(BlockPlaceEvent $event){			//放置方块事件
			$block = $event->getBlock();
			$blockID = $event->getBlock()->getID();
			$player = $event->getPlayer();
			$array = $this->Config->get('禁止摆放的创造方块');
			$spy = $this->Config->get('被监控的方块');
			$pos_name = $block->x.$block->y.$block->z;
			if(!$event->getPlayer()->getGamemode()==0){
				if(in_array($blockID,$array)){			//判断获取的方块ID是否存在于数组中
					$event->setCancelled();
					$player->sendMessage('§b［创造控制中心］§e您在创造模式下无法放置该方块');
				}
				if(in_array($blockID,$spy)){			//判断获取的方块ID是否存在于数组中
					$player->sendMessage('§b［创造控制中心］§e您在创造模式下放置的该敏感方块将被记录');
					$this->Record->set($pos_name,['玩家'.$player->getName().'在X:'.$block->x.'Y:'.$block->y.'Z:'.$block->z.'放置过敏感创造方块'.$blockID]);
					$this->Record->save();
				}
			}
	}
}